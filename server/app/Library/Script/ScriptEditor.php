<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script;

use Lang;

/**
 * Класс обслуживает script-editor.js
 *
 * @author soliton
 */
class ScriptEditor 
{
    /**
     * Создает набор списков: keywords, functions, strings
     * 
     * @return type
     */
    static public function makeKeywords() 
    {
        // Обращаемся к транслятору за списками ключевых слов
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
        foreach(\App\Http\Models\VariablesModel::orderBy('name', 'asc')->get() as $row) {
            $strings[$row->name] = $row->comm.' '.Lang::get('admin/hubs.app_control.'.$row->app_control);
        }
        
        return (object)[
            'keywords' => $keywords,
            'functions' => $functions,
            'strings' => $strings,
        ];
    }
}
