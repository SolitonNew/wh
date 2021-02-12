<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OwManagerController extends Controller
{
    public function index() {
        return view('admin.ow-manager', [
            
        ]);
    }
}
