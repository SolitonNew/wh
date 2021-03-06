<?php

namespace App\Http\Controllers\Admin\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PowerController extends Controller
{
    public function index() {
        return view('admin.statistics.power.statistics-power', [
            
        ]);
    }
}
