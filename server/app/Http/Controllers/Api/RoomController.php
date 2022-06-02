<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RoomService;

class RoomController 
{
    private $_service;
    
    public function __construct(RoomService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @param int $roomID
     * @return type
     */
    public function getData(int $roomID)
    {
        $data = $this->_service->getData($roomID);
        return response()->json($data);
    }
}
