<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use View;

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
        // Вычитываем все нужные данные
        $owList = \App\Http\Models\OwDevsModel::orderBy('ID', 'asc')->get();
        $varList = \App\Http\Models\VariablesModel::orderBy('ID', 'asc')->get();
        $scriptList = \App\Http\Models\ScriptsModel::orderBy('ID', 'asc')->get();
        
        // 
        foreach($varList as &$row) {
            $row->OW_INDEX = -1;
            if ($row->OW_ID) {
                for ($i = 0; $i < count($owList); $i++) {
                    if ($row->OW_ID == $owList[$i]->ID) {
                        $row->OW_INDEX = $i;
                        break;
                    }
                }
            }
        }
        
        $variableNames = [];
        foreach($varList as $row) {
            $variableNames[] = $row->NAME;
        }
        
        foreach($scriptList as &$row) {
            $translator = new Script\Translate(new Script\Translators\C($variableNames), $row->DATA);
            $row->DATA_TO_C = $translator->run();
        }

        // Пакуем в файл
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config.h', View::make('admin.configuration.config', [
            'owList' => $owList,
            'varList' => $varList,
            'scriptList' => $scriptList,
            'varTyps' => [
                'pyb' => 0,
                'ow' => 1,
                'variable' => 2,
            ]
        ]));
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
