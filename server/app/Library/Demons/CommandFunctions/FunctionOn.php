<?php

namespace App\Library\Demons\CommandFunctions;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

trait FunctionOn {
    /**
     * 
     * @param type $name
     */
    public function function_on($name) {
        $this->function_set($name, 1);
    }
}