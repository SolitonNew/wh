<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HubsController extends Controller
{
    public function index(int $hubID = null) {
        if (!$hubID) {
            $hubID = \App\Http\Models\ControllersModel::whereIsServer(0)->orderBy('name', 'asc')->first();
            if ($hubID) {
                $hubID = $hubID->id;
            } else {
                $hubID = null;
            }
        }
        
        return redirect(route('admin.hub-devices', [$hubID]));
    }
    
    public function edit(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
