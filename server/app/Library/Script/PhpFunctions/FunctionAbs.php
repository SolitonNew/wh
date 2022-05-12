<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

trait FunctionAbs 
{
    /**
     * 
     * @param type $value
     * @return type
     */
    public function function_abs_i($value) 
    {
        return (int)abs($value);
    }
    
    /**
     * 
     * @param type $value
     * @return type
     */
    public function function_abs_f($value) 
    {
        return abs($value);
    }
}
