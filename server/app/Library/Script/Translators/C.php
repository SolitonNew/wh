<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\Translators;

/**
 * Description of C
 *
 * @author soliton
 */
class C implements ITranslator 
{
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
            '+' => 'command_speech',
        ],
        'play' => [
            '+' => 'command_play',
        ],
        'info' => [
            0 => 'command_info',
        ],
        'print_i' => [
            1 => 'command_print_i',
        ],
        'print_f' => [
            1 => 'command_print_f',
        ],
        'print_s' => [
            1 => 'command_print_s',
        ],
        'abs_i' => [
            1 => 'command_abs_i',
        ],
        'abs_f' => [
            1 => 'command_abs_f',
        ],
        'round' => [
            1 => 'command_round',
        ],
        'ceil' => [
            1 => 'command_ceil',
        ],
        'floor' => [
            1 => 'command_floor',
        ],
    ];
    
    private $_variableNames = [];
    
    public function __construct($variableNames = []) 
    {
        $this->_variableNames = $variableNames;
    }
    
    
    /**
     * 
     * @param type $parts
     */
    public function translate($prepareData) 
    {
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
        
        for ($i = 0; $i < count($parts); $i++) {
            if (is_object($parts[$i])) {
                if (isset($this->_functions[$parts[$i]->name])) {
                    if (isset($this->_functions[$parts[$i]->name]['+'])) {
                        $args = $parts[$i]->args;
                        $parts[$i] = $this->_functions[$parts[$i]->name]['+'];
                        for (; $i < count($parts); $i++) {
                            if ($parts[$i] == '(') {
                                $parts[$i] = '('.$args.', ';
                                break;
                            }
                        }
                    } else {
                        $parts[$i] = $this->_functions[$parts[$i]->name][$parts[$i]->args];
                    }
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
