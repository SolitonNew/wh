<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;
use App\Models\Schedule;

trait FunctionSet
{
    /**
     * @param string $name
     * @param float $value
     * @param int $time
     * @return void
     * @throws \Exception
     */
    public function function_set(string $name, float $value, int $time = 0): void
    {
        $device = Device::whereName($name)->first();
        if ($device) {
            if ($this->fake) {
                //
            } else {
                if ($time == 0) {
                    Device::setValue($device->id, $value, 0);
                } else {
                    $datetime = now()->addSecond($time);
                    Schedule::appendFastRecord("set('$name', $value, $time)", "set('$name', $value);", $datetime, $device->id);
                }
            }
        } else {
            throw new \Exception("Variable '$name' not found");
        }
    }
}
