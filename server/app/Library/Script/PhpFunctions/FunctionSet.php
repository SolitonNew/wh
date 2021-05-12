<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;
use App\Models\SchedulerModel;
use DB;

trait FunctionSet 
{
    /**
     * 
     * @param type $name
     * @param type $value
     * @throws \Exception
     */
    public function function_set(string $name, float $value, int $time = 0) 
    {
        $variable = Device::whereName($name)->first();
        if ($variable) {
            if ($this->_fake) {
                //
            } else {
                if ($time == 0) {
                    DB::select('CALL CORE_SET_DEVICE('.$variable->id.', '.$value.', null)');
                } else {
                    $datetime = now()->addMinute($time);
                    SchedulerModel::appendFastRecord("set('$name', $value, $time)", "set('$name', $value);", $datetime, $variable->id);
                }
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}