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
        
        $query = \App\Http\Models\VariableChangesModel::whereVariableId($id);
        
        if (Session::get('DATE')) {
            $d = Carbon::parse(Session::get('DATE'))->startOfDay();
            $query->whereBetween('CHANGE_DATE', [$d, $d->copy()->addDay()]);
        }
        
        if (Session::get('SQL')) {
            $query->whereRaw('VALUE '.Session::get('SQL'));
        }
        
        $errors = [];
        try {
            $data = $query->orderBy('ID', 'asc')->get();
        } catch (\Exception $ex) {
            $errors['SQL'] = $ex->getMessage();
            $data = [];
        }
        
        return view('admin.statistics.table.statistics-table', [
            'id' => $id,
            'data' => $data,
        ])->withErrors($errors);
    }
}
