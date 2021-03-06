<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script;

/**
 * Description of PhpExecute
 *
 * @author soliton
 */
class PhpExecute {
    use PhpFunctions\FunctionGet,
        PhpFunctions\FunctionInfo,
        PhpFunctions\FunctionOff,
        PhpFunctions\FunctionOn,
        PhpFunctions\FunctionPlay,
        PhpFunctions\FunctionPrint,
        PhpFunctions\FunctionSet,
        PhpFunctions\FunctionSpeech,
        PhpFunctions\FunctionToggle;
            
    /**
     *
     * @var type 
     */
    protected $_translator;
    
    /**
     * 
     * @param type $source
     */
    public function __construct($source) {
        $this->_translator = new Translate(new Translators\Php(), $source);
    }
    
    /**
     *
     * @var type 
     */
    protected $_outLines = [];
    
    /**
     * 
     * @return type
     */
    public function run() {
        try {
            $code = $this->_translator->run();
            eval($code);
        } catch (\ParseError $ex) {
            $this->printLine($ex->getMessage());
        } catch (\Throwable $ex) {
            $this->printLine($ex->getMessage());
        }
        
        return implode("\n", $this->_outLines);
    }
    
    /**
     * 
     * @param type $text
     */
    public function printLine($text) {
        $this->_outLines[] = $text;
    }
}
