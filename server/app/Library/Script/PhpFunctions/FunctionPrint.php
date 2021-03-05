<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

trait FunctionPrint {
    /**
     * 
     * @param type $text
     */
    public function function_print($text) {
        $this->printLine('>>> '.$text);
    }
}
