<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CamsController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        return view('admin.cams', [
            
        ]);
    }
}