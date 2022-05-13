<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

trait FunctionRound
{
    /**
     * 
     * @param type $value
     * @return type
     */
    public function function_round($value) 
    {
        return (int)round($value);
    }
}
