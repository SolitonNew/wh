<?php

namespace App\Http\Services\Admin;

use Session;

class DevicesService 
{
    const FILTER_ROOM = 'DEVICES_room_id';
    
    /**
     * 
     * @param string $roomID
     * @return string
     */
    public function prepareRoomFilter(string $roomID = null)
    {
        if (!$roomID) {
            $roomID = Session::get(self::FILTER_ROOM);
        }
        
        if (!$roomID) {
            $roomID = 'none';
        }
        
        Session::put(self::FILTER_ROOM, $roomID);
        
        return $roomID;
    }
}
