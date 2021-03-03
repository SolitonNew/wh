<?php

namespace App\Http\Controllers\Admin\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Carbon\Carbon;
use Session;

class TableController extends Controller
{
    public function index(Request $request, int $id = null) {
        
        if ($request->method() == 'POST') {
            Session::put('DATE', $request->post('DATE'));
            Session::put('SQL', $request->post('SQL'));
        }
        
        $data = \App\Http\Models\VariableChangesModel::whereVariableId($id);
        
        if (Session::get('DATE')) {
            $d = Carbon::parse(Session::get('DATE'))->startOfDay();
            $data->whereBetween('CHANGE_DATE', [$d, $d->copy()->addDay()]);
        }
        
        if (Session::get('SQL')) {
            $data->whereRaw('VALUE '.Session::get('SQL'));
        }
        
        return view('admin.statistics.table.statistics-table', [
            'id' => $id,
            'data' => $data->orderBy('ID', 'asc')->get(),
        ]);
    }
}
