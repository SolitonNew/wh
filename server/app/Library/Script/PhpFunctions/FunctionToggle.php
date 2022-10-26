<?php

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;
use App\Models\Schedule;

trait FunctionToggle
{
    /**
     * @param string $name
     * @param int $time
     * @return void
     * @throws \Exception
     */
    public function function_toggle(string $name, int $time = 0): void
    {
        $device = Device::whereName($name)->first();
        if ($device) {
            if ($this->fake) {
                //
            } else {
                if ($time == 0) {
                    if ($device->value) {
                        Device::setValue($device->id, 0);
                    } else {
                        Device::setValue($device->id, 1);
                    }
                } else {
                    $datetime = now()->addSeconds($time);
                    Schedule::appendFastRecord("toggle('$name', value, $time)", "toggle('$name');", $datetime, $device->id);
                }
            }
        } else {
            throw new \Exception("Device '$name' not found");
        }
    }
}
