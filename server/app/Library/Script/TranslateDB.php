<?php

namespace App\Library\Script;

use App\Models\ScriptString;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class TranslateDB extends Translate
{
    /**
     * 
     * @param type $parts
     * @param type $strings
     */
    protected function _prepareStrings(&$parts, &$strings)
    {
        $devices = Device::get();
        
        for ($i = 0; $i < count($parts); $i++) {
            if (!is_object($parts[$i]) && isset($strings[$parts[$i]])) {
                $data = substr($parts[$i], 1, strlen($parts[$i]) - 2);
                
                // Search device by name
                $isDevice = false;
                foreach ($devices as $device) {
                    if ($device->name == $data) {
                        $isDevice = true;
                        break;
                    }
                }
                
                // Ignore device name
                if (!$isDevice) {
                    $parts[$i] = ScriptString::setData($data);
                }
            }
        }
    }
}
