<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Description of Firmware
 *
 * @author soliton
 */
class Firmware {
    
    protected $_project = 'din_master';
    protected $_mmcu = 'atmega8a';
    protected $_rel_path = 'devices/din_master/firmware';
    
    /**
     * Абсолютный путь к директории прошивки
     */
    protected function _firmwarePath() {
        $path = explode('/', base_path());
        array_pop($path);
        $path[] = $this->_rel_path;
        return implode('/', $path);
    }
    
    /**
     * Создает файл настройки для включения в прошивку.
     * Файл помещается по пути din_master
     */
    public function generateConfig() {
        $firmwarePath = $this->_firmwarePath();       
        
        // Вычитываем все нужные данные
        $owList = \App\Http\Models\OwDevsModel::orderBy('ID', 'asc')->get();
        $varList = \App\Http\Models\VariablesModel::orderBy('ID', 'asc')->get();
        $scriptList = \App\Http\Models\ScriptsModel::orderBy('ID', 'asc')->get();
        
        // Собираем файл
        $file = fopen($firmwarePath.'/_config.h', 'w+');
        fwrite($file, 
"#include <avr/pgmspace.h>

typedef struct variable {
    int id;
    unsigned char controller;
    unsigned char typ;       // 0-pyb;1-ow;2-variable
    unsigned char direction; // 0, 1
    char name[24];
    int ow_index;            // Порядковый номер в массиве ow_roms
    unsigned char channel;   // порядковый номе канала
}\n");
        fwrite($file, "\n");
        
        // Грузим список OW устройств
        fwrite($file, "const unsigned char ow_roms[".(count($owList) * 8)."] PROGMEM = {\n");
        foreach($owList as $row) {
            $rom = sprintf("0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X,", 
                $row->ROM_1, 
                $row->ROM_2, 
                $row->ROM_3, 
                $row->ROM_4, 
                $row->ROM_5, 
                $row->ROM_6, 
                $row->ROM_7,
                $row->ROM_8
            );
            fwrite($file, "    $rom\n");
        }
        fwrite($file, "};\n");
        fwrite($file, "\n");
        
        // Грузим список переменных
        $typs = [
            'pyb' => 0,
            'ow' => 1,
            'variable' => 2,
        ];
        fwrite($file, "const struct variable variables[".(count($varList) * 1)."] PROGMEM = {\n");
        foreach($varList as $row) {
            $typ = $typs[$row->ROM];
            $ow_index = -1;
            for($i = 0; $i < count($owList); $i++) {
                if ($owList[$i]->ID === $row->OW_ID) {
                    $ow_index = $i;
                    break;
                }
            }
            $channel = 0;
            fwrite($file, "    $row->ID, $row->CONTROLLER_ID, $typ, $row->DIRECTION, $ow_index, $channel,\n");
        }
        fwrite($file, "};\n");
        fwrite($file, "\n");
        
        // Грузим список значений переменных
        fwrite($file, "float variablesValue[".count($varList)."];\n");
        fwrite($file, "\n");
        
        // Грузим список сценариев
        
        $variableNames = [];
        foreach($varList as $row) {
            $variableNames[] = $row->NAME;
        }
        
        foreach($scriptList as $row) {
            fwrite($file, "void script_$row->ID(void) {\n");
            $translator = new Script\Translate(new Script\Translators\C($variableNames), $row->DATA);
            fwrite($file, $translator->run());
            fwrite($file, "}\n");
            fwrite($file, "\n");
        }
        fwrite($file, "\n");
        
        fclose($file);
    }
    
    /**
     * Выполняет необходимые действия с компилятором avr-gcc для получения 
     * прошивки.
     * 
     * @param type $outs
     * @return boolean    true - OK; false - ERROR
     */
    public function make(&$outs) {
        $firmwarePath = $this->_firmwarePath();
        
        $path_c = $firmwarePath.'/'.$this->_project.'.c';
        $release_path = $firmwarePath.'/Release';
        if (!file_exists($release_path)) {
            mkdir($release_path);
        }        
        
        $path_o = $release_path.'/'.$this->_project.'.o';
        $path_elf = $release_path.'/'.$this->_project.'.elf';
        $path_hex = $release_path.'/'.$this->_project.'.hex';
        
        $commands = [
            "avr-gcc -funsigned-char -funsigned-bitfields -Os -fpack-struct -fshort-enums -Wall -c -std=gnu99 -MD -MP -mmcu=$this->_mmcu -o $path_o $path_c",
            "avr-gcc -o $path_elf $path_o -Wl,-Map=\"din_master.map\" -Wl,-lm -mmcu=$this->_mmcu ",
            "avr-objcopy -O ihex -R .eeprom -R .fuse -R .lock -R .signature  $path_elf $path_hex",
            "avr-size -C --mcu=$this->_mmcu $path_elf"
        ];
        
        for($i = 0; $i < count($commands); $i++) {
            exec($commands[$i].' 2>&1', $outs);
            if (count($outs)) {
                return ($i == count($commands) - 1);
            }
        }
        
        return false;
    }
}
