<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use Log;


class ScriptParser {
    
    // Ключевые слова которые точно не переменные
    protected $_specKeys = [
        'else',
        'break',
        'default',
        'int',
        'string',
        'float',
        'byte',
        'char',
    ];
    
    /**
     *
     * @var type 
     */
    protected $_source = '';
    
    /**
     *
     * @var type 
     */
    protected $_keywords = [];
    
    /**
     * 
     * @param type $source
     * @param type $keywords
     * @param type $funcPrefix
     */
    public function __construct($source, $keywords) {
        $this->_source = $source;
        $this->_keywords = $keywords;
        $this->_parse();
    }
    
    /**
     *
     * @var type 
     */
    protected $_parts = [];
    
    /**
     *  Разбираем исходный текст на части. 
     */
    protected function _parse() {
        // Разделитель для фрагментации исходного кода
        $delimeters = [
            ' ',
            ';',
            ',',
            '"',
            "'",
            '+',
            '-',
            '*',
            '/',
            '=',
            '(',
            ')',
            '{',
            '}',
            ':',
            ';',
            '?',
            '&',
            '|',
            '!',
            '$',
            chr(10),
            chr(13),
            chr(9),  // tab
        ];
        
        $this->_parts = [];
        
        $s = '';
        for($i = 0; $i < strlen($this->_source); $i++) {
            $c = $this->_source[$i];
            if (in_array($c, $delimeters)) {
                if ($s !== '') {
                    $this->_parts[] = $s;
                }
                $s = '';
                $this->_parts[] = $c;
            } else {
                $s .= $c;
            }
        }
        
        if ($s !== '') {
            $this->_parts[] = $s;
        }
    }
    
    /**
     *  Собираем исходный код на основе структуры с учетом особенностей PHP
     */
    public function convertToPhp($funcPrefix) {
        $res = [];
        
        // Чистим от коментариев
        $len = count($this->_parts);
        for($i = 0; $i < $len; $i++) {
            $p = $this->_parts[$i];
            
            $append = false;
            if ($p === '/') { // Возможно начало коментария
                if ($i < $len - 1) {
                    if ($this->_parts[$i + 1] == '*') { // Начало многострочного коментария
                        for ($k = $i + 1; $k < $len - 1; $k++) {
                            if ($this->_parts[$k] === '*' && $this->_parts[$k + 1] === '/') { // Коментарий закончился
                                $i = $k + 1;
                                $append = true; // Пометим что уже обработали
                                break;
                            }
                        }
                    } else
                    if ($this->_parts[$i + 1] == '/') { // Начало однострочного коментария
                        for ($k = $i + 1; $k < $len - 1; $k++) {
                            if ($this->_parts[$k] == chr(10) || $this->_parts[$k] == chr(13)) { // Коментарий закончился
                                $i = $k;
                                $append = true; // Пометим что уже обработали
                                break;
                            }
                        }
                    }
                }
            }
            
            if (!$append) {
                $res[] = $p;
            }
        }
        
        // Обрабатываем переменные и функции
        $len = count($res);
        for($i = 0; $i < $len; $i++) {
            $p = $res[$i];
            
            $append = false;
            
            if ($p === '"' || $p === "'") { // Возможно начало строки
                $t = $p; // Помечаем с чего началась строка
                for ($k = $i + 1; $k < $len; $k++) {
                    if ($res[$k] === $t) { // Текст закончился
                        $i = $k;
                        $append = true; // Пометим что уже обработали
                        break;
                    }
                }
            }
            
            if ($append) continue; // Уже обработали
            
            if (preg_match('/[0-9]/', $p)) { // Начинается с числа
                $append = true;
            } else 
            if (preg_match('/[a-zA-Z]/', $p)) { // Начинается с символа. Посмотрим подробней
                for ($k = $i + 1; $k < $len; $k++) {
                    if ($res[$k] === ' ') {
                        // пропускаем
                    } else
                    if ($res[$k] === '(') { // Нашли функцию
                        if (in_array($p, $this->_keywords)) { // Нашли нашу функцию
                            $res[$i] = $funcPrefix.$p;
                        } else {
                            //
                        }
                        $append = true;
                        break;
                    } else {
                        break;
                    }
                }
                
                // Если ничего не добавили рассматриваем поближе
                if (!$append) {
                    if (in_array($p, $this->_specKeys)) { // Это специальное ключевое слово
                        //
                    } else { // Иначе это переменная
                        $res[$i] = '$'.$p;
                    }
                    $append = true;
                }
            }
        }
        
        return implode('', $res);
    }
    
    /**
     *  Собираем исходный код на основе структуры с учетом особенностей C
     */
    public function convertToC($funcPrefix) {
        
    }
}