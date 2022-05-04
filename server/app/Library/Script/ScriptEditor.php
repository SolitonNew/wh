<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script;

use App\Library\Script\PhpExecute;
use App\Models\Device;
use Illuminate\Support\Facades\Lang;

/**
 * Класс обслуживает script-editor.js
 *
 * @author soliton
 */
class ScriptEditor 
{
    /**
     * Creating lists: keywords, functions, strings
     * 
     * @return type
     */
    static public function makeKeywords() 
    {
        // Referring to the translator for keyword lists
        $translate = new \App\Library\Script\Translate('');
        
        $keywords = [];        
        foreach($translate->getKeywords() as $key) {
            $keywords[$key] = 'keyword';
        }
        
        $functions = [];
        foreach ($translate->getFunctions() as $key => $val) {
            $functions[$key] = $val['helper'];
        }
        
        $strings = [];
        foreach (Device::orderBy('name', 'asc')->get() as $row) {
            $strings[$row->name] = $row->comm.' '.Lang::get('admin/hubs.app_control.'.$row->app_control);
        }
        
        return (object)[
            'keywords' => $keywords,
            'functions' => $functions,
            'strings' => $strings,
        ];
    }
    
    /**
     * 
     * @param string $command
     * @return type
     */
    static public function scriptTest(string $command)
    {
        try {
            $execute = new PhpExecute($command);
            $report = [];
            $res = $execute->run(true, $report);
            
            $log = [];
            $log[] = 'Testing completed successfully';
            $log[] = str_repeat('-', 40);
            $log[] = 'FUNCTIONS ['.count($report['functions']).']';
            foreach ($report['functions'] as $key => $val) {
                $log[] = '    '.$key;
            }
            $log[] = '';
            if ($res) {
                $log[] = 'Stream out';
                $log[] = str_repeat('-', 40);
                $log[] = $res;
            }
            
            return implode("\n", $log);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
