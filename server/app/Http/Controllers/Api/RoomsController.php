<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RoomsService;

class RoomsController
{
    /**
     * @var RoomsService 
     */
    private $service;

    public function __construct(RoomsService $service)
    {
        $this->service = $service;
    }

    /**
     *
     * @return type
     */
    public function getData()
    {
        $data = $this->service->getData();
        return response()->json($data);
    }
}
