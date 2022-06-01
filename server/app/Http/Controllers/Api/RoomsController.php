<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RoomsService;

class RoomsController 
{
    private $_service;
    
    public function __construct(RoomsService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @return type
     */
    public function getData()
    {
        $data = $this->_service->getData();
        return response()->json($data);
    }
}
