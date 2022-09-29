<?php

namespace App\Services\Admin;

class DevicesService
{
    const FILTER_ROOM = 'DEVICES_room_id';

    /**
     * @param string|null $roomID
     * @return string
     */
    public function prepareRoomFilter(string|null $roomID = null): string
    {
        if (!$roomID) {
            $roomID = isset($_COOKIE[self::FILTER_ROOM]) ? $_COOKIE[self::FILTER_ROOM] : '';
        }

        if (!$roomID) {
            $roomID = 'none';
        }

        return $roomID;
    }
}
