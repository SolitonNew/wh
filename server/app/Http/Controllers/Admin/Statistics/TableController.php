<?php

namespace App\Http\Controllers\Admin\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TableController extends Controller
{
    public function index(Request $request, int $id = null) {
        
        if ($request->method() == 'POST') {
            
        }
        
        return view('admin.statistics.table.statistics-table', [
            'id' => $id,
        ]);
    }
}
