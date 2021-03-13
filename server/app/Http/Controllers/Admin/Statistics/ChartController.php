<?php

namespace App\Http\Controllers\Admin\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChartController extends Controller
{
    public function index() {
        
        $panels = []; // \App\Http\Models\WebStatPanelsModel::orderBy('ID', 'asc')->get();
        
        return view('admin.statistics.chart.statistics-chart', [
            'panels' => $panels,
        ]);
    }
}
