<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\EventsService;

class EventsController extends Controller
{
    private $_service;
    
    public function __construct(EventsService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * 
     * @param int $lastID
     * @return type
     */
    public function getData(int $lastID)
    {
        if ($lastID > -1) {
            $data = $this->_service->getEvents($lastID);
            return response()->json($data);
        } else {
            return response()->json([
                'lastID' => $this->_service->getLastID(),
            ]);
        }        
    }
}