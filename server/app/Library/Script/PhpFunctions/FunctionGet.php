<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;

trait FunctionGet 
{
    /**
     * 
     * @param type $name
     * @return type
     * @throws \Exception
     */
    public function function_get($name) 
    {
        $variable = Device::whereName($name)->first();
        
        if ($variable) {
            return $variable->value;
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}