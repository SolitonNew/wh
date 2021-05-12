<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;
use App\Models\SchedulerModel;

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
        $device = Device::whereName($name)->first();
        if ($device) {
            if ($this->_fake) {
                //
            } else {
                if ($time == 0) {
                    Device::setValue($device->id, $value);
                } else {
                    $datetime = now()->addMinute($time);
                    SchedulerModel::appendFastRecord("set('$name', $value, $time)", "set('$name', $value);", $datetime, $device->id);
                }
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}