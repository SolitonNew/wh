<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Http\Services\Terminal\RoomService;

class RoomController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_roomService;
    
    /**
     * 
     * @param RoomService $roomService
     */
    public function __construct(RoomService $roomService) {
        $this->_roomService = $roomService;
    }
    
    /**
     * Route to view the list of devices.
     * 
     * @param type $roomID
     * @return type
     */
    public function index(int $roomID) 
    {        
        list ($roomTitle, $rows, $charts, $varSteps) = $this->_roomService->roomData($roomID);
                
        return view('terminal.room', [
            'roomTitle' => $roomTitle,
            'rows' => $rows,
            'charts' => $charts,
            'varSteps' => $varSteps,
        ]);
    }
}
