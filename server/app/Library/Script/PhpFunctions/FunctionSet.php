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
    public function function_set(string $name, float $value, int $time = 0) {
        $variable = \App\Http\Models\VariablesModel::whereName($name)->first();
        if ($variable) {
            if ($this->_fake) {
                //
            } else {
                if ($time == 0) {
                    DB::select('CALL CORE_SET_VARIABLE('.$variable->id.', '.$value.', null)');
                } else {
                    $datetime = now()->addMinute($time);
                    \App\Http\Models\SchedulerModel::appendFastRecord("set('$name', $value, $time)", "set('$name', $value);", $datetime, $variable->id);
                }
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}