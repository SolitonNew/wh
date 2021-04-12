<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HubsController extends Controller
{
    public function index() {
        return redirect(route('admin.devices'));
    }
    
    public function edit(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
