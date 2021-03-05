<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script;

/**
 * Description of ScriptExecute
 *
 * @author soliton
 */
class Translate {
    /**
     *
     * @var type 
     */
    protected $_translator;
    
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
     * @param ITranslator $translator
     * @param string $source
     */
    public function __construct(Translators\ITranslator $translator, string $source) {
        $this->_translator = $translator;
        $this->_source = $source;
        $this->_split();
    }
    
    /**
     *  Разбираем исходный текст на части. 
     */
    protected function _split() {
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
     * 
     */
    public function run() {
        return $this->_translator->translate($this->_parts);
    }

}
