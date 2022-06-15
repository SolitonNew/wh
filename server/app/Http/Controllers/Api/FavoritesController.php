<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\FavoritesService;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    private $_service;
    
    public function __construct(FavoritesService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @return type
     */
    public function getData(Request $request)
    {
        $data = $this->_service->getData();
        
        foreach ($data as $device) {
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
