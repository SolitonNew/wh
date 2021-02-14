<?php

namespace App\Http\COntrollers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function index() {
        return view('admin.schedule', [
            
        ]);
    }
}
