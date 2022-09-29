<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RoomService;
use Illuminate\Http\Request;

class RoomController
{
    /**
     * @var RoomService 
     */
    private $service;

    public function __construct(RoomService $service)
    {
        $this->service = $service;
    }

    /**
     *
     * @param int $roomID
     * @return type
     */
    public function getData(Request $request, int $roomID)
    {
        $data = $this->service->getData($roomID, $request->getHttpHost(), $request->api_token);
        return response()->json($data);
    }
}
