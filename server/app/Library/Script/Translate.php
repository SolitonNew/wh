<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script;

use App\Library\Script\Translators\ITranslator;
use Log;

/**
 * Description of Translate
 *
 * @author soliton
 */
class Translate {
    /**
     * Cловарь синтаксических конструкций
     * @var type 
     */
    private $_keywords = [
        'if',
        'else',
        'break',
        'switch',
        'case',
        'default',
        'for',
    ];
    
    /**
     * Словарь функций
     * 
     * $key - Имя функции
     * $val - Количество параметров
     * 
     * @var type 
     */
    private $_functions = [
        'get' => [1],
        'set' => [2, 3],
        'on' => [1, 2],
        'off' => [1, 2],
        'toggle' => [1, 2],
        'speech' => [1],
        'play' => [1],
        'info' => [0],
        //'loop' => [1],
    ];
    
    /**
     *
     * @var type 
     */
    protected $_source;
    
    /**
     *
     * @var type 
     */
    protected $_parts = [];
    
    /**
     * 
     * @param type $source
     */
    public function __construct($source) {
        $this->_source = $source;
        $this->_split();
        $this->_prepare();
    }
    
    /**
     *  Разбираем исходный текст на части. 
     */
    protected function _split() {
        // Разделитель для фрагментации исходного кода
        $delimeters = [
            ' ' => [],
            ';' => [],
            ',' => [],
            '"' => [],
            "'" => [],
            '+' => ['+', '='],
            '-' => ['-', '='],
            '*' => ['=', '/'],
            '/' => ['=', '/', '*'],
            '=' => ['='],
            '(' => [],
            ')' => [],
            '{' => [],
            '}' => [],
            '[' => [],
            ']' => [],
            ':' => [],
            ';' => [],
            '?' => [],
            '&' => ['&'],
            '|' => ['|'],
            '!' => [],
            '$' => [],
            chr(10) => [],
            chr(13) => [],
            chr(9) => [],  // tab
        ];
        
        $this->_parts = [];
        
        $s = '';
        for($i = 0; $i < strlen($this->_source); $i++) {
            $c = $this->_source[$i];
            if (isset($delimeters[$c])) {
                if ($s !== '') {
                    $this->_parts[] = $s;
                }
                $s = '';
                if (count($delimeters[$c]) && ($i < strlen($this->_source) - 1)) {
                    $cn = $this->_source[$i + 1];
                    if (in_array($cn, $delimeters[$c])) {
                        $s = $c.$cn;
                        $i++;
                    }
                } 
                if ($s === '') {
                    $this->_parts[] = $c;
                } else {
                    $this->_parts[] = $s;
                }
                $s = '';
            } else {
                $s .= $c;
            }
        }
        
        if ($s !== '') {
            $this->_parts[] = $s;
        }
    }
    
    
    private $_prepared_functions = [];
    private $_prepared_variables = [];
    private $_prepared_numbers = [];  
    private $_prepared_strings = [];    
    
    private function _prepareBlock($from_i, &$func_args) {
        $spaces = ['', ' ', chr(9), chr(10), chr(13)];
            
        switch ($this->_parts[$from_i]) {
            case '(': 
                $to_char = ')';
                $from_i++;
                break;
            case '{': 
                $to_char = '}';
                $from_i++;
                break;
            default: 
                $to_char = chr(0);
        }
        
        for ($i = $from_i; $i < count($this->_parts); $i++) {
            $part = $this->_parts[$i];
            if (in_array($part, $spaces)) continue;
            
            if ($part == $to_char) { // Конец блока
                return $i;
            } else
            if ($part == '{') { // Новый блок
                $args = 0;
                $i = $this->_prepareBlock($i, $args);
            } else
            if ($part == ',') {
                $func_args++;
            } else
            if (preg_match('/[0-9]/', $part[0])) { // Это число
                $this->_prepared_numbers[$part] = (isset($this->_prepared_numbers[$part]) ? $this->_prepared_numbers[$part] + 1 : 1);
            } else
            if (preg_match('/[a-zA-Z]/', $part[0])) { // Это функция, фраза или переменная
                if ($i < count($this->_parts) - 1) {
                    $is_keyword = false;
                    for ($k = $i + 1; $k < count($this->_parts); $k++) {
                        if (in_array($this->_parts[$k], $spaces)) continue;
                        if ($this->_parts[$k] == '(') { // Это функция или конструкция
                            $args = 1;
                            $new_i = $this->_prepareBlock($k, $args);
                            
                            if (isset($this->_functions[$part])) { // Это наша функция
                                // Проверяем кол-во аргументов
                                if (!in_array($args, $this->_functions[$part])) {
                                    throw new \Exception('Invalid number of arguments "'.$args.'" for "'.$part.'"');
                                }
                                // Подменяем строку записи на объект с расширеной информацией
                                $this->_parts[$i] = (object)[
                                    'type' => 'function',
                                    'name' => $part,
                                    'args' => $args,
                                ];
                                
                                Log::info($part . $args);
                                
                                if (isset($this->_prepared_functions[$part])) {
                                    if (!in_array($args, $this->_prepared_functions[$part])) {
                                        $this->_prepared_functions[$part][] = $args;
                                    }
                                } else {
                                    $this->_prepared_functions[$part][] = $args;
                                }
                            } else 
                            if (!in_array($part, $this->_keywords)) {
                                throw new \Exception('Unknown function "'.$part.'"');
                            }
                            $is_keyword = true;
                            
                            $i = $new_i;
                        } else 
                        if (in_array($part, $this->_keywords)) {
                            $is_keyword = true;
                        }
                        break;                        
                    }
                    if (!$is_keyword) {
                        $this->_prepared_variables[$part] = (isset($this->_prepared_variables[$part]) ? $this->_prepared_variables[$part] + 1 : 1);
                    }
                } else {
                    $this->_prepared_variables[$part] = (isset($this->_prepared_variables[$part]) ? $this->_prepared_variables[$part] + 1 : 1);
                }
            }
        }
        
        return count($this->_parts) - 1;
    }
    
    /**
     * 
     */
    private function _prepare() {
        $this->_prepared_functions = [];
        $this->_prepared_variables = [];
        $this->_prepared_numbers = [];
        $this->_prepared_strings = [];
        
        // Чистим коментарии
        for ($i = 0; $i < count($this->_parts); $i++) {
            $part = $this->_parts[$i];
            if ($part == '/*') { // Начало многострочного коментария
                $this->_parts[$i] = '';
                for ($k = $i + 1; $k < count($this->_parts); $k++) {
                    if ($this->_parts[$k] == '*/') {
                        $this->_parts[$k] = '';
                        $i = $k + 1;
                        break;
                    }
                    $this->_parts[$k] = '';
                }
            } else
            if ($part == '//') { // Коментарий до конца строки
                for ($k = $i; $k < count($this->_parts); $k++) {
                    if ($this->_parts[$k] == chr(10) || $this->_parts[$k] == chr(13)) {
                        $i = $k;
                        break;
                    } else {
                        $this->_parts[$k] = '';
                    }
                }
            }
        }
        
        // Собираем строки
        for ($i = 0; $i < count($this->_parts); $i++) {
            $part = $this->_parts[$i];
            if ($part == '"' || $part == "'") {
                $from_char = $part;
                $string = [$part];
                for ($k = $i + 1; $k < count($this->_parts); $k++) {
                    $string[] = $this->_parts[$k];
                    if ($this->_parts[$k] == $from_char) {
                        $this->_parts[$k] = '';
                        $str = implode('', $string);
                        $this->_prepared_strings[$str] = (isset($this->_prepared_strings[$str]) ? $this->_prepared_strings[$str] + 1 : 1);
                        $this->_parts[$i] = $str;
                        $i = $k + 1;
                        break;
                    }
                    $this->_parts[$k] = '';
                }
            }
        }
        
        $args = 0;
        $this->_prepareBlock(0, $args);
    }
    
    /**
     * Собирает исходный код из частей спользуя указаный транслятор.
     * 
     * @param ITranslator $translator
     * @param array $report
     * @return string
     */
    public function run(ITranslator $translator, &$report = null) {
        $parts = [];
        foreach($this->_parts as $part) {
            if ($part != '') {
                $parts[] = $part;
            }
        }
        
        $prepareData = (object)[
            'parts' =>  $parts, 
            'functions' => $this->_prepared_functions, 
            'variables' => $this->_prepared_variables, 
            'strings' => $this->_prepared_strings, 
            'numbers' => $this->_prepared_numbers,
        ];
        
        return $translator->translate($prepareData, $report);
    }
}
