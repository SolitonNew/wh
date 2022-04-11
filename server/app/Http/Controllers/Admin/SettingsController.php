<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingsService;

class SettingsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_service;
    
    /**
     * 
     * @param SettingsService $service
     */
    public function __construct(SettingsService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * Index route of the terminal settings module.
     * 
     * @return type
     */
    public function index()
    {
        $levels = $this->_service->levels();
        $currentLevel = $this->_service->getCurrentLevel();
        
        return view('admin.settings.settings', [
            'levels' => $levels,
            'maxLevel' => $currentLevel,
        ]);
    }
    
    /**
     * This route is used to set the maximum value of the visible level of the 
     * plan_rooms structure for the terminal module.
     * 
     * @param type $value
     * @return string
     */
    public function setMaxLevel($value) {
        $this->_service->setCurrentLevel($value);
        
        return 'OK';
    }
}
