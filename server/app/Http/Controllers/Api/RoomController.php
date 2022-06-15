<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RoomService;
use Illuminate\Http\Request;

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
    public function getData(Request $request, int $roomID)
    {
        $data = $this->_service->getData($roomID);
        
        foreach ($data->devices as $device) {
            if (isset($device->camcorderData)) {
                $device->camcorderData->thumbnail = route('cam-thumbnail', [
                    'id' => $device->camcorderData->id, 
                    'api_token' => $request->get('api_token')
                ]);
                $host = 'http://'.$request->getHttpHost();
                $device->camcorderData->video = $host.':'.(10000 + $device->camcorderData->id);
            }
        }
        
        return response()->json($data);
    }
}
