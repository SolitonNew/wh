<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Script\PhpFunctions;

use App\Models\Device;

trait FunctionToggle 
{
    /**
     * 
     * @param type $name
     */
    public function function_toggle($name, $time = 0) 
    {
        $device = Device::whereName($name)->first();
        if ($device) {
            if ($this->_fake) {
                //
            } else {
                if ($device->value) {
                    Device::setValue($device->id, 0);
                } else {
                    Device::setValue($device->id, 1);
                }
            }
        } else {
            throw new \Exception("Device '$name' not found");
        }
    }
}