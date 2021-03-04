<?php

namespace App\Library\Demons\CommandFunctions;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use DB;

trait FunctionSet {
    /**
     * 
     * @param type $name
     * @param type $value
     * @throws \Exception
     */
    public function function_set($name, $value) {
        $vars = DB::select("select ID from core_variables where NAME = '$name'");
        if (count($vars)) {
            DB::select('CALL CORE_SET_VARIABLE('.$vars[0]->ID.', '.$value.', null)');
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}