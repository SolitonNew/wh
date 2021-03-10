<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use View;
use Log;

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
        $scriptList = \App\Http\Models\ScriptsModel::orderBy('ID', 'asc')->limit(10)->get();
        
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
        
        // Проверяем наличие директории config
        if (!file_exists($this->_firmwarePath().'/config')) {
            mkdir($this->_firmwarePath().'/config');
        }

        // Пакуем в файл config.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config/devs.h', View::make('admin.configuration.config.devs_h', [
            'owList' => $owList,
            'varList' => $varList,
            'varTyps' => [
                'pyb' => 0,
                'ow' => 1,
                'variable' => 2,
            ]
        ]));
        
        // Пакуем в файл config.h
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config/scripts.h', View::make('admin.configuration.config.scripts_h', [
            'scriptList' => $scriptList,
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
        
        // Получаем файлы проекта
        $xml = simplexml_load_file($firmwarePath.'/'.$this->_project.'.cproj');
        
        $files = [];
        foreach($xml->ItemGroup[0]->Compile as $item) {
            $file = (string)$item['Include'];
            if (strpos($file, '.c') === strlen($file) - 2) {
                $files[] = str_replace('\\', '/', $file);
            }
        }
        
        $folders = [];
        foreach($xml->ItemGroup[1]->Folder as $item) {
            $folder = (string)$item['Include'];
            $folders[] = str_replace('\\', '/', $folder);
        }
        
        // Проверяем наличие или создаем нужные директории
        $release_path = $firmwarePath.'/Release';
        if (!file_exists($release_path)) {
            mkdir($release_path);
        }
        
        // Проверяем наличе или создаем поддиректории
        foreach($folders as $folder) {
            if (!file_exists($release_path.'/'.$folder)) {
                mkdir($release_path.'/'.$folder);
            }
        }
        
        $commands = [];
        
        // Собираем комманды для компиляции .c файлов        
        foreach($files as $file) {
            $path_c = $firmwarePath.'/'.$file;
            $path_o = $release_path.'/'.substr($file, 0, strlen($file) - 2).'.o';
            $commands[] = "avr-gcc -funsigned-char -funsigned-bitfields -Os -fpack-struct -fshort-enums -Wall -c -std=gnu99 -MD -MP -mmcu=$this->_mmcu -o $path_o $path_c";
        }
        
        // Собираем комманды линковки
        $path_elf = $release_path.'/'.$this->_project.'.elf';
        $files_o = [];
        foreach($files as $file) {
            $files_o[] = $release_path.'/'.substr($file, 0, strlen($file) - 2).'.o';
        }
        $path_o_all = implode(' ', $files_o);
        $commands[] = "avr-gcc -o $path_elf $path_o_all -Wl,-Map=\"din_master.map\" -Wl,-lm -mmcu=$this->_mmcu ";
        
        // Команда создания прошивки
        $path_hex = $release_path.'/'.$this->_project.'.hex';
        $commands[] = "avr-objcopy -O ihex -R .eeprom -R .fuse -R .lock -R .signature  $path_elf $path_hex";
                    
        
        // Команда сбора статистики
        $commands[] = "avr-size -C --mcu=$this->_mmcu $path_elf";
        
        // Запускаем созданые команды на выполнение
        for($i = 0; $i < count($commands); $i++) {
            //Log::info($commands[$i]);
            exec($commands[$i].' 2>&1', $outs);
            if (count($outs)) {
                return ($i == count($commands) - 1);
            }
        }
        
        return false;
    }
}
