<?php

namespace App\Services\Terminal;

use App\Models\Device;
use App\Models\EventMem;
use DB;

class DeviceService 
{
    /**
     * 
     * @param type $deviceID
     * @return string
     */
    public function showHosticeView($deviceID)
    {
        $sql = "select p.name group_title, v.comm device_title, v.app_control, v.room_id, v.value ".
               "  from core_devices v, plan_rooms p ".
               " where v.id = $deviceID ".
               "   and p.id = v.room_id";        
        $row = DB::select($sql)[0];
        
        $roomID = $row->room_id;
        $roomTitle = mb_strtoupper($row->group_title);
        $deviceTitle = $row->device_title;
        $control = Device::decodeAppControl($row->app_control);
        $deviceTitle = Device::roomDeviceName($roomTitle, mb_strtoupper($deviceTitle), $control->label);

        
        switch ($control->typ) {
            case 1:
                return 'ERROR';
            case 2:
                return 'ERROR';
            case 3:
                return view('terminal.device_3', [
                    'roomID' => $roomID,
                    'roomTitle' => $roomTitle,
                    'deviceTitle' => $deviceTitle,
                    'deviceID' => $deviceID,
                    'deviceValue' => $row->value,
                    'control' => $control,
                ]);
        }
    }
    
    /**
     * 
     * @param type $lastID
     * @return type
     */
    public function getChanges($lastID)
    {
        if ($lastID > 0) {
            $res = DB::select("select c.id, c.device_id, c.value, UNIX_TIMESTAMP(c.created_at) * 1000 created_at ".
                              "  from core_events_mem c ".
                              " where c.id > $lastID ".
                              " order by c.id");
            return response()->json($res);
        } else {
            return 'LAST_ID: '.(EventMem::lastDeviceChangeID() ?? -1);
        }
    }
    
    public function setValue(int $deviceID, int $value)
    {
        Device::setValue($deviceID, $value);
    }
}
