<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

trait FunctionOn {
    /**
     * 
     * @param type $name
     */
    public function function_on($name, $time = 0) {
        $this->function_set($name, 1, $time);
    }
}