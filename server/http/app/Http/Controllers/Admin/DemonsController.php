<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DemonsController extends Controller
{
    private $_demons = [
        'rs485-demon' => 'RS485',
        'schedule-demon' => 'SCHEDULE',
        'executor-demon' => 'EXEC',
        'watcher-demon' => 'WATCH',
    ];
    
    public function index(int $id = null) {
        
        return view('admin.demons', [
            'demons' => $this->_demons,
        ]);
    }
}
