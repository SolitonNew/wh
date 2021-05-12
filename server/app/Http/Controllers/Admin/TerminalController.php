<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\Admin\TerminalService;

class TerminalController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_terminalService;
    
    /**
     * 
     * @param TerminalService $terminalService
     */
    public function __construct(TerminalService $terminalService) 
    {
        $this->_terminalService = $terminalService;
    }
    
    /**
     * Index route of the terminal settings module.
     * 
     * @return type
     */
    public function index()
    {
        $levels = $this->_terminalService->levels();
        $currentLevel = $this->_terminalService->getCurrentLevel();
        
        return view('admin.terminal.terminal', [
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
        $this->_terminalService->setCurrentLevel($value);
        
        return 'OK';
    }
}
