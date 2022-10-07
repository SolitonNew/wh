<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\DeviceService;

class DeviceController extends Controller
{
    /**
     * @var DeviceService
     */
    private DeviceService $service;

    public function __construct(DeviceService $service)
    {
        $this->service = $service;
    }

    /**
     *
     * @param int $deviceID
     * @return type
     */
    public function getData(int $deviceID)
    {
        $data = $this->service->getData($deviceID);
        return response()->json($data);
    }

    /**
     *
     * @param Request $request
     * @param int $deviceID
     */
    public function setData(Request $request, int $deviceID)
    {
        $this->service->setValue($deviceID, $request->value);
    }
}
