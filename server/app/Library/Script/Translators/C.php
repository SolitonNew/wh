<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\Translators;

use Log;

class C implements ITranslator {
    
    // Ключевые слова которые точно не переменные
    protected $_specKeys = [
        'else',
        'break',
        'default',
    ];
    
    protected $_types = [
        'int',
        'string',
        'float',
        'byte',
        'char',
    ];
    
    /**
     * Зарезервированные короткие команды.
     * В тексте скрипта команды будут заменены на аналогичные присоединенные 
     * методы спрефиксом $this->function_[command].
     * 
     * @var type 
     */
    protected $_keywords = [
        'get' => 'command_get', 
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
        'speech' => 'command_speach',
        'play' => 'command_play',
        'info' => 'command_info',
        'print' => 'command_print',
    ];    
    
    /**
     *
     * @var type 
     */
    protected $_variableNames = [];
    
    /**
     * 
     * @param type $variables
     */
    public function __construct($variableNames) {
        $this->_variableNames = $variableNames;
    }
    
    
    /**
     * 
     * @param type $parts
     * @return type
     */
    public function translate(&$parts) {
        $res = [];
        
        // Чистим от коментариев
        $len = count($parts);
        for($i = 0; $i < $len; $i++) {
            $p = $parts[$i];
            
            $append = false;
            if ($p === '/') { // Возможно начало коментария
                if ($i < $len - 1) {
                    if ($parts[$i + 1] == '*') { // Начало многострочного коментария
                        for ($k = $i + 1; $k < $len - 1; $k++) {
                            if ($parts[$k] === '*' && $parts[$k + 1] === '/') { // Коментарий закончился
                                $i = $k + 1;
                                $append = true; // Пометим что уже обработали
                                break;
                            }
                        }
                    } else
                    if ($parts[$i + 1] == '/') { // Начало однострочного коментария
                        for ($k = $i + 1; $k < $len - 1; $k++) {
                            if ($parts[$k] == chr(10) || $parts[$k] == chr(13)) { // Коментарий закончился
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
                        // Склеиваем строку
                        $str_a = [];
                        for ($v = $i + 1; $v < $k; $v++) {
                            $str_a[] = $res[$v];
                        }
                        $str = implode('', $str_a);
                        
                        // Ищем в списке названий переменных. 
                        // Если есть - заменяем название на индекс                        
                        $v_index = array_search($str, $this->_variableNames);
                        
                        if ($v_index !== false) { // Это название переменной. Меняем на индекс
                            $res[$i] = $v_index;
                            for ($v = $i + 1; $v <= $k; $v++) {
                                $res[$v] = '';
                            }
                        }
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
                        if (isset($this->_keywords[$p])) { // Нашли нашу функцию
                            // Определяем может ли функция иметь несколько вариантов
                            if (is_array($this->_keywords[$p])) {
                                // считаем кол-во параметров
                                $a_c = 1;
                                for ($a_i = $k + 1; $a_i < $len; $a_i++) {
                                    if ($res[$a_i] === ',') $a_c++;
                                    if ($res[$a_i] === ')') break;
                                }
                                Log::info($a_c);
                                if (isset($this->_keywords[$p][$a_c])) {
                                    $res[$i] = $this->_keywords[$p][$a_c];
                                } else { // Такое тоже может быть. Пусть компилятор разбирается
                                    $res[$i] = $this->_keywords[$p][array_key_first($this->_keywords[$p])];
                                }
                            } else {
                                $res[$i] = $this->_keywords[$p];
                            }
                        } else {
                            //
                        }
                        $append = true;
                        break;
                    } else {
                        break;
                    }
                }
                
                // Если ничего не добавили рассматриваем по ближе
                if (!$append) {
                    if (in_array($p, $this->_specKeys)) { // Это специальное ключевое слово
                        //
                    } else
                    if (in_array($p, $this->_types)) { // Это тип... если обрамлен скобками - убираем их
                        for ($k = $i - 1; $k > -1; $k--) {
                            if ($res[$k] === ' ') {
                                
                            } else {
                                if ($res[$k] === '(') {
                                    $res[$k] = ' ';
                                }
                                break;
                            }
                        }
                        
                        for ($k = $i + 1; $k < $len; $k++) {
                            if ($res[$k] === ' ') {
                                
                            } else {
                                if ($res[$k] === ')') {
                                    $res[$k] = ' ';
                                }
                                break;
                            }
                        }
                        
                    } else { // Иначе это переменная
                        //
                    }
                    $append = true;
                }
            }
        }
        
        return implode('', $res);
    }
}