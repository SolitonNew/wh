<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostsController extends Controller
{
    public function index(int $hubID = null) {
        if (!\App\Http\Models\ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        return view('admin.hubs.hosts.hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => [],
        ]);
    }
    
    public function info(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
