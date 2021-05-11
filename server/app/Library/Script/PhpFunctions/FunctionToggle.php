<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use App\Models\VariablesModel;
use DB;

trait FunctionToggle 
{
    /**
     * 
     * @param type $name
     */
    public function function_toggle($name, $time = 0) 
    {
        $variable = VariablesModel::whereName($name)->first();
        if ($variable) {
            if ($this->_fake) {
                //
            } else {
                if ($variable->value) {
                    DB::select('CALL CORE_SET_VARIABLE('.$variable->id.', 0, null)');
                } else {
                    DB::select('CALL CORE_SET_VARIABLE('.$variable->id.', 1, null)');
                }
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}