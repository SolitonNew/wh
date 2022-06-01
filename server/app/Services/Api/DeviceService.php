<?php

namespace App\Services\Api;

use App\Models\Device;
use Illuminate\Support\Facades\DB;

class DeviceService 
{
    /**
     * 
     * @param int $deviceID
     * @return type
     */
    public function getData(int $deviceID)
    {
        $sql = "select p.name group_title, v.comm device_title, v.app_control, v.room_id, v.value ".
               "  from core_devices v, plan_rooms p ".
               " where v.id = $deviceID ".
               "   and p.id = v.room_id";        
        $row = DB::select($sql)[0];
        
        $roomID = $row->room_id;
        $roomTitle = mb_strtoupper($row->group_title);
        $control = Device::decodeAppControl($row->app_control);        
        $deviceTitle = Device::roomDeviceName($roomTitle, mb_strtoupper($row->device_title), $control->label);
        
        switch ($control->typ) {
            case 1:
            case 2:
                break;
            case 3:
                return (object)[
                    'room' => (object)[
                        'id' => $roomID,
                        'title' => $roomTitle,
                    ],
                    'device' => (object)[
                        'id' => $deviceID,
                        'title' => trim($deviceTitle),
                        'value' => $row->value,
                        'control' => $control,
                    ]
                ];
        }
        
        return null;
    }
    
    /**
     * 
     * @param int $deviceID
     * @param int $value
     */
    public function setValue(int $deviceID, int $value)
    {
        Device::setValue($deviceID, $value);
    }
}
