<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Services\Terminal\DeviceService;

class DeviceController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_deviceService;
    
    /**
     * 
     * @param DeviceService $deviceService
     */
    public function __construct(DeviceService $deviceService) 
    {
        $this->_deviceService = $deviceService;
    }
    
    /**
     * This route is for the device management page.
     * 
     * @param type $deviceID
     * @return string
     */
    public function index($deviceID) 
    {
        return $this->_deviceService->showHosticeView($deviceID);
    }
    
    /**
     * This route return the latest device changes.
     * 
     * @param type $lastID
     * @return type
     */
    public function changes(int $lastID) 
    {
        return $this->_deviceService->getChanges($lastID);
    }
    
    /**
     * This route sets the device value.
     * 
     * @param type $deviceID
     * @param type $value
     * @return string
     */
    public function set(int $deviceID, int $value) 
    {
        $this->_deviceService->setValue($deviceID, $value);
        
        return '';
    }
}
