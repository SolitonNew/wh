<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DevicesController extends Controller
{
    public function index(int $nubId = null) {
        $hub = null;
        if (!$hubId) {
            $nub = \App\Http\Models\ControllersModel::orderBy('rom', 'asc')->first();
            if ($hub) {
                return redirect(route('admin.devices', $hub->id));
            }
        }
        
        return view('admin.hubs.devices', [
            'hubId' => $hubId,
            'hub' => $hub,
        ]);
    }
    
    public function edit(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
