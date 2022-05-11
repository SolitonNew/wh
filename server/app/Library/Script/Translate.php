<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script;

use App\Library\Script\Translators\ITranslator;
use Illuminate\Support\Facades\Log;

/**
 * Description of Translate
 *
 * @author soliton
 */
class Translate 
{
    /**
     * Dictionary of syntactic constructions.
     * 
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
     * Dictionary of syntactic constructions.
     * 
     * @return type
     */
    public function getKeywords() 
    {
        return $this->_keywords;
    }
    
    /**
     * Dictionary of functions
     *
     * $key - Function name
     * helper - Description of the function for the script editor
     * args - Possible number of parameters
     * 
     * @var type 
     */    
    private $_functions = [
        'get' => [
            'helper' => 'function (name)',
            'args' => [1],
        ],
        'set' => [
            'helper' => 'function (name, value, later = 0)',
            'args' => [2, 3],
        ],
        'on' => [
            'helper' => 'function (name, later = 0)',
            'args' => [1, 2],
        ],
        'off' => [
            'helper' => 'function (name, later = 0)',
            'args' => [1, 2],
        ],
        'toggle' => [
            'helper' => 'function (name, later = 0)',
            'args' => [1, 2],
        ],
        'speech' => [
            'helper' => 'function (phrase, args)',
            'args' => ['1+'],
        ],
        'play' => [
            'helper' => 'function (media, args)',
            'args' => ['1+'],
        ],
        'info' => [
            'helper' => 'function ()',
            'args' => [0],
        ],
        'print_i' => [
            'helper' => 'function (int)',
            'args' => [1],
        ],
        'print_f' => [
            'helper' => 'function (float)',
            'args' => [1],
        ],
        'print_s' => [
            'helper' => 'function (text)',
            'args' => [1],
        ]
    ];
    
    /**
     * Dictionary of functions
     *
     * $key - Function name
     * helper - Description of the function for the script editor
     * args - Possible number of parameters
     * 
     * @var type 
     */
    public function getFunctions() 
    {
        return $this->_functions;
    }
    
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
     * @param type $stringsHandler
     */
    public function __construct($source) 
    {
        $this->_source = $source;
        $this->_split();
        $this->_prepare();
    }
    
    /**
     * Breaks the source code into pieces.
     */
    protected function _split() 
    {
        // Separator for fragmenting source code.
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
    
    private function _prepareBlock($from_i, &$func_args) 
    {
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
        
        $empty = 0;
        for ($i = $from_i; $i < count($this->_parts); $i++) {
            $part = $this->_parts[$i];
            if (in_array($part, $spaces)) continue;
            
            $empty++;
            
            if ($part == $to_char) { // End block
                if ($empty == 1) $func_args = 0;
                return $i;
            } else
            if ($part == '{') { // New block
                $args = 0;
                $i = $this->_prepareBlock($i, $args);
            } else
            if ($part == ',') {
                $func_args++;
            } else
            if (preg_match('/[0-9]/', $part[0])) { // It is a number
                $this->_prepared_numbers[$part] = (isset($this->_prepared_numbers[$part]) ? $this->_prepared_numbers[$part] + 1 : 1);
            } else
            if (preg_match('/[a-zA-Z]/', $part[0])) { // It is a function, phrase or variable
                if ($i < count($this->_parts) - 1) {
                    $is_keyword = false;
                    for ($k = $i + 1; $k < count($this->_parts); $k++) {
                        if (in_array($this->_parts[$k], $spaces)) continue;
                        if ($this->_parts[$k] == '(') { // It is a function or construction
                            $args = 1;
                            $new_i = $this->_prepareBlock($k, $args);                            
                            if (isset($this->_functions[$part])) { // It is a function
                                // Check the number of arguments
                                if (strpos($this->_functions[$part]['args'][0], '+') !== false) {
                                    $minArgs = substr($this->_functions[$part]['args'][0], 0, strlen($this->_functions[$part]['args'][0]) - 1) ?: 0;
                                    if ($minArgs > $args) {
                                        throw new \Exception('Invalid number of arguments "'.$args.'" for "'.$part.'"');
                                    }
                                } else
                                if (!in_array($args, $this->_functions[$part]['args'])) {
                                    throw new \Exception('Invalid number of arguments "'.$args.'" for "'.$part.'"');
                                }                                
                                
                                // Replace the record string with an object 
                                // with extended information.
                                $this->_parts[$i] = (object)[
                                    'type' => 'function',
                                    'name' => $part,
                                    'args' => $args,
                                ];
                                
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
    private function _prepare() 
    {
        $this->_prepared_functions = [];
        $this->_prepared_variables = [];
        $this->_prepared_numbers = [];
        $this->_prepared_strings = [];
        
        if (count($this->_parts) == 0) return ;
        
        // Deleting comments
        for ($i = 0; $i < count($this->_parts); $i++) {
            $part = $this->_parts[$i];
            if ($part == '/*') { // Start of a multi-line comment
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
            if ($part == '//') { // Comment in line
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
        
        // Make strings
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
     * 
     * @param type $parts
     * @param type $strings
     */
    protected function _prepareStrings(&$parts, &$strings)
    {
        //
    }
    
    /**
     * Builds source code from parts using the specified translator.
     * 
     * @param ITranslator $translator
     * @param array $report
     * @return string
     */
    public function run(ITranslator $translator, &$report = null) 
    {
        $parts = [];
        foreach($this->_parts as $part) {
            if ($part !== '') {
                $parts[] = $part;
            }
        }
        
        $this->_prepareStrings($parts, $this->_prepared_strings);
        
        $prepareData = (object)[
            'parts' =>  $parts, 
            'functions' => $this->_prepared_functions, 
            'variables' => $this->_prepared_variables, 
            'strings' => $this->_prepared_strings, 
            'numbers' => $this->_prepared_numbers,
        ];
        
        if (is_array($report)) {
            $report['functions'] = $this->_prepared_functions;
            $report['variables'] = $this->_prepared_variables;
            $report['strings'] = $this->_prepared_strings;
            $report['numbers'] = $this->_prepared_numbers;
        }
        
        return $translator->translate($prepareData);
    }
}
