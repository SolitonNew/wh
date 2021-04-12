<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\Translators;

use Log;

/**
 * Description of Php$prepareData$prepareData$prepareData
 *
 * @author soliton
 */
class Php implements ITranslator {
    /**
     *
     * @var type 
     */
    private $_functions = [
        'get' => [
            1 => '$this->function_get',
        ],
        'set' => [
            2 => '$this->function_set',
            3 => '$this->function_set',
        ],
        'on' => [
            1 => '$this->function_on',
            2 => '$this->function_on',
        ],
        'off' => [
            1 => '$this->function_off',
            2 => '$this->function_off',
        ],
        'toggle' => [
            1 => '$this->function_toggle',
            2 => '$this->function_toggle',
        ],
        'speech' => [
            1 => '$this->function_speech',
        ],
        'play' => [
            1 => '$this->function_play',
        ],
        'info' => [
            0 => '$this->function_info',
        ],
    ];    
    
    /**
     * 
     * @param type $parts
     */
    public function translate($prepareData) {
        $parts = $prepareData->parts;
        $variables = $prepareData->variables;
        
        for($i = 0; $i < count($parts); $i++) {
            if (is_object($parts[$i])) {
                if (isset($this->_functions[$parts[$i]->name])) {
                    $parts[$i] = $this->_functions[$parts[$i]->name][$parts[$i]->args];
                } else {
                    $parts[$i] = $parts[$i]->name;
                }
            } else 
            if (isset($variables[$parts[$i]])) {
                $parts[$i] = '$'.$parts[$i];
            }
        }
        
        return implode('', $parts);
    }
}
