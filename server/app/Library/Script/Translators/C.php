<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\Translators;

use Log;

/**
 * Description of C
 *
 * @author soliton
 */
class C implements ITranslator {
    /**
     *
     * @var type 
     */
    private $_functions = [
        'get' => [
            1 => 'command_get',
        ],
        'set' => [
            2 => 'command_set',
            3 => 'command_set_later',
        ],
        'on' => [
            1 => 'command_on',
            2 => 'command_on_later',
        ],
        'off' => [
            1 => 'command_off',
            2 => 'command_off_later',
        ],
        'toggle' => [
            1 => 'command_toggle',
            2 => 'command_toggle_later',
        ],
        'speech' => [
            1 => 'command_speech',
        ],
        'play' => [
            1 => 'command_play',
        ],
        'info' => [
            0 => 'command_info',
        ],
    ];
    
    private $_variableNames = [];
    
    public function __construct($variableNames = []) {
        $this->_variableNames = $variableNames;
    }
    
    
    /**
     * 
     * @param type $parts
     */
    public function translate($prepareData) {
        $parts = $prepareData->parts;
        
        $variables = [];
        foreach($prepareData->variables as $var => $v) {
            $variables[] = 'int '.$var.";\n";
        }
        
        $varIDs = [];
        foreach($prepareData->strings as $str => $v) {
            $s = substr($str, 1, strlen($str) - 2);
            $i = array_search($s, $this->_variableNames);
            if ($i !== false) {
                $varIDs[$str] = $i;
            }
        }
        
        for($i = 0; $i < count($parts); $i++) {
            if (is_object($parts[$i])) {
                if (isset($this->_functions[$parts[$i]->name])) {                    
                    $parts[$i] = $this->_functions[$parts[$i]->name][$parts[$i]->args];
                } else {
                    $parts[$i] = $parts[$i]->name;
                }
            } else 
            if (isset($varIDs[$parts[$i]])) {
                $parts[$i] = $varIDs[$parts[$i]];
            }
        }
        
        return implode('', $variables)."\n".implode('', $parts);
    }
}
