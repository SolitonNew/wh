<?php

namespace App\Services\Admin;

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
            $roomID = isset($_COOKIE[self::FILTER_ROOM]) ? $_COOKIE[self::FILTER_ROOM] : '';
        }
        
        if (!$roomID) {
            $roomID = 'none';
        }
        
        //Session::put(self::FILTER_ROOM, $roomID);
        
        return $roomID;
    }
}
