<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use DB;

trait FunctionToggle {
    /**
     * 
     * @param type $name
     */
    public function function_toggle($name, $time = 0) {
        $vars = DB::select("select ID, VALUE from core_variables where NAME = '$name'");
        if (count($vars)) {
            if ($vars[0]->VALUE) {
                DB::select('CALL CORE_SET_VARIABLE('.$vars[0]->ID.', 0, null)');
            } else {
                DB::select('CALL CORE_SET_VARIABLE('.$vars[0]->ID.', 1, null)');
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}