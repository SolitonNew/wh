<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConfigurationController extends Controller
{
    public function index(int $id = null) {
        return view('admin.configuration.configuration', [
            
        ]);
    }
}
