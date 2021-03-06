<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use DB;

trait FunctionSet {
    /**
     * 
     * @param type $name
     * @param type $value
     * @throws \Exception
     */
    public function function_set($name, $value, $time = 0) {
        $vars = DB::select("select ID from core_variables where NAME = '$name'");
        if (count($vars)) {
            if ($time == 0) {
                DB::select('CALL CORE_SET_VARIABLE('.$vars[0]->ID.', '.$value.', null)');
            } else {
                $datetime = now()->addMinute($time);
                \App\Http\Models\SchedulerModel::appendFastRecord("set('$name', $value, $time)", "set('$name', $value);", $datetime, $vars[0]->ID);
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}