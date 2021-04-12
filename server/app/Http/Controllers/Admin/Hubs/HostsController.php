<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostsController extends Controller
{
    public function index(int $hubId = null) {
        $hub = null;
        if (!$hubId) {
            $nub = \App\Http\Models\ControllersModel::orderBy('rom', 'asc')->first();
            if ($hub) {
                return redirect(route('admin.hosts', $hub->id));
            }
        }
        
        return view('admin.hubs.hosts', [
            'hubId' => $hubId,
            'hub' => $hub,
        ]);
    }
    
    public function info(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
