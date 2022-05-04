<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Services\Terminal\RoomsService;

class RoomsController extends Controller
{   
    /**
     *
     * @var type 
     */
    private $_roomsService;
    
    /**
     * 
     * @param RoomsService $roomsService
     */
    public function __construct(RoomsService $roomsService ) 
    {
        $this->_roomsService = $roomsService;
    }
    
    /**
     * Route to view an ordered list of rooms and titled devices.
     * 
     * @return type
     */
    public function index() 
    {
        $data = $this->_roomsService->roomsData();
        $columnCount = $this->_roomsService->roomsColumnCount($data);
        
        return view('terminal.rooms', [
            'data' => $data,
            'columnCount' => $columnCount,
        ]);
    }
}
