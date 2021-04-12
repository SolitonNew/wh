<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DevicesController extends Controller
{
    public function index(int $hubID = null) {
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
        ]);
    }
    
    public function edit(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
