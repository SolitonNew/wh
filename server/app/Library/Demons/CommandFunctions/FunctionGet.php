<?php

namespace App\Library\Demons\CommandFunctions;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use DB;

trait FunctionGet {
    /**
     * 
     * @param type $name
     * @return type
     * @throws \Exception
     */
    public function function_get($name) {
        $vars = DB::select("select VALUE from core_variables where NAME = '$name'");
        if (count($vars)) {
            return $vars[0]->VALUE;
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}