<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use View;
use Log;
use DB;

/**
 * Description of Firmware
 *
 * @author soliton
 */
class Firmware 
{
    /**
     * Название проекта прошивки щитового контроллера
     * 
     * @var type 
     */
    protected $_project = 'din_master';
    
    /**
     * Название микроконтроллера
     * 
     * @var type 
     */
    protected $_mmcu = 'atmega8a';
    
    /**
     * Размер страницы микроконтроллера.
     * Зависит от типа микроконтроллера и устанавливается вручную.
     * Влияет на паузы при прошивке микроконтроллера (после передачи одной страницы 
     * нужно сделать паузу, что бы контроллер успел записать во flash)
     * 
     * @var type 
     */
    protected $_spm_pagesize = 128;
    
    public function spmPageSize() 
    {
        return $this->_spm_pagesize;
    }
    
    /**
     * Путь к папке с исходниками прошивки
     * @var type 
     */
    protected $_rel_path = 'devices/din_master/firmware';
    
    /**
     * 
     */
    public function __construct() 
    {
        $this->_mmcu = config('firmware.mmcu');
        $this->_spm_pagesize = config('firmware.spm_pagesize');
    }
    
    /**
     * Абсолютный путь к директории прошивки
     */
    protected function _firmwarePath() 
    {
        $path = explode('/', base_path());
        array_pop($path);
        $path[] = $this->_rel_path;
        return implode('/', $path);
    }
    
    /**
     * Создает файл настройки для включения в прошивку.
     * Файл помещается по пути din_master
     */
    public function generateConfig() 
    {
        // Вычитываем все нужные данные
        $owDevTyps = \App\Http\Models\OwTypesModel::orderBy('code', 'asc')->get();
        $owList = \App\Http\Models\OwDevsModel::orderBy('id', 'asc')->get();
        $varList = DB::select('select v.*, c.rom controller_rom
                                 from core_variables v, core_controllers c
                                where v.controller_id = c.id
                               order by v.id');
        $scriptList = \App\Http\Models\ScriptsModel::orderBy('id', 'asc')->get();
        $eventList = DB::select('select e.variable_id, GROUP_CONCAT(e.script_id) script_ids
                                   from core_variable_events e 
                                 group by e.variable_id 
                                 order by e.variable_id');
        
        foreach($varList as $row) {
            $row->ow_index = -1;
            if ($row->typ == 'din') {
                $c = array_search($row->channel, ['R1', 'R2', 'R3', 'R4']);
                if ($c !== false) {
                    $row->channel = $c;
                } else {
                    $row->channel = 0;
                }
            } elseif ($row->typ == 'ow') {
                if ($row->ow_id) {
                    // Проставляем индекс OW
                    $owCode = -1;
                    for ($i = 0; $i < count($owList); $i++) {
                        if ($row->ow_id == $owList[$i]->id) {
                            $row->ow_index = $i;
                            $owCode = $owList[$i]->rom_1;
                            break;
                        }
                    }

                    // Проставляем индекс канала
                    if ($owCode > 0) {
                        foreach($owDevTyps as $devTyp) {
                            if ($devTyp->code == $owCode) {
                                $a = explode(',', $devTyp->channels);
                                $c = array_search($row->channel, $a);
                                if ($c !== false) {
                                    $row->channel = $c;
                                } else {
                                    $row->channel = 0;
                                }
                            }
                        }
                    } else {
                        $row->channel = 0;
                    }
                }
            } else {
                $row->channel = 0;
            }
        }
        
        $variableNames = [];
        foreach($varList as $row) {
            $variableNames[] = $row->name;
        }
        
        foreach($scriptList as &$row) {
            $translator = new Script\Translate($row->data);
            $report = [];
            $row->data_to_c = $translator->run(new Script\Translators\C($variableNames), $report);
        }
        
        // Проставляем индексы для переменных в связях с эвентами
        foreach($eventList as &$row) {
            $varIndex = -1;
            for ($i = 0; $i < count($varList); $i++) {
                if ($varList[$i]->id === $row->variable_id) {
                    $varIndex = $i;
                    break;
                }
            }
            $row->variableIndex = $varIndex;
        }
        
        // Проверяем наличие директории config
        if (!file_exists($this->_firmwarePath().'/config')) {
            mkdir($this->_firmwarePath().'/config');
        }
        
        // Пакуем в файл devs.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config/devs.h', View::make('admin.config.devs_h', [
            'owList' => $owList,
            'varList' => $varList,
        ]));

        // Пакуем в файл devs.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config/devs.c', View::make('admin.config.devs_c', [
            'owList' => $owList,
            'varList' => $varList,
            'varTyps' => [
                'din' => 0,
                'ow' => 1,
                'variable' => 2,
            ],
        ]));
        
        // Пакуем в файл scripts.h
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config/scripts.h', View::make('admin.config.scripts_h', [
            'scriptList' => $scriptList,
        ]));
        
        // Пакуем в файл scripts.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->_firmwarePath().'/config/scripts.c', View::make('admin.config.scripts_c', [
            'scriptList' => $scriptList,
            'eventList' => $eventList,
        ]));
    }
    
    /**
     * Выполняет необходимые действия с компилятором avr-gcc для получения 
     * файла прошивки в формате IntelHEX.
     * Результат работы будет помещен в подпапку Release.
     * 
     * @param type $outs
     * @return boolean    true - OK; false - ERROR
     */
    public function make(&$outs) 
    {
        $firmwarePath = $this->_firmwarePath();
        
        // Получаем файлы проекта
        $xml = simplexml_load_file($firmwarePath.'/'.$this->_project.'.cproj');
        
        $files = [];
        foreach($xml->ItemGroup[0]->Compile as $item) {
            $file = (string)$item['Include'];
            if (strpos($file, '.c') === strlen($file) - 2) {
                //if ($file == 'lcd.c') continue;
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
        $path_map = $release_path.'/'.$this->_project.'.map';
        $path_elf = $release_path.'/'.$this->_project.'.elf';
        $files_o = [];
        foreach($files as $file) {
            $files_o[] = $release_path.'/'.substr($file, 0, strlen($file) - 2).'.o';
        }
        $path_o_all = implode(' ', $files_o);
        $commands[] = "avr-gcc -o $path_elf $path_o_all -Wl,-Map=\"$path_map\" -Wl,-lm -mmcu=$this->_mmcu ";
        
        // Команда создания прошивки
        $path_hex = $release_path.'/'.$this->_project.'.hex';
        $commands[] = "avr-objcopy -O ihex -R .eeprom -R .fuse -R .lock -R .signature  $path_elf $path_hex";
                    
        
        // Команда сбора статистики
        $commands[] = "avr-size -C --mcu=$this->_mmcu $path_elf";
        
        // Запускаем созданые команды на выполнение
        for($i = 0; $i < count($commands); $i++) {
            exec($commands[$i].' 2>&1', $outs);
            if (count($outs)) {
                return ($i == count($commands) - 1);
            }
        }
        
        return false;
    }
    
    /**
     * Читает ранее созданый файл прошивки и формирует массив по 8 байт в записи.
     * 
     * @return boolean|array
     */
    public function getHex()
    {
        $file = $this->_firmwarePath().'/Release/'.$this->_project.'.hex';
        if (!file_exists($file)) return false;
        
        $res = [];
        
        $f = fopen($file, 'r');
        try {
            $d_i = 0;
            $data = [];
            while (!feof($f)) {
                $line = fgets($f);
                $len = hexdec(substr($line, 1, 2));
                for ($i = 0; $i < $len; $i++) {
                    $data[] = hexdec(substr($line, 9 + $i * 2, 2));
                    $d_i++;
                    if ($d_i >= 8) {
                        if (count($data)) {
                            $res[] = $data;
                        }
                        $d_i = 0;
                        $data = [];
                    }
                }
            }
            if (count($data)) {
                $res[] = $data;
            }
        } catch (\Exception $ex) {

        } finally {
            fclose($f);
        }
        
        return $res;
    }
}
