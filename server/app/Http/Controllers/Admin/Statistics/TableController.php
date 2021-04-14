<?php

namespace App\Http\Controllers\Admin\Statistics;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Carbon\Carbon;
use Session;

class TableController extends Controller
{
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return type
     */
    public function index(Request $request, int $id = null) 
    {
        if ($id) {
            Session::put('STATISTICS-TABLE-ID', $id);
        } else {
            if (Session::get('STATISTICS-TABLE-ID')) {
                return redirect(route('statistics-table', Session::get('STATISTICS-TABLE-ID')));
            }
        }
        
        if ($request->method() == 'POST') {
            Session::put('STATISTICS-TABLE-DATE', $request->post('date'));
            Session::put('STATISTICS-TABLE-SQL', $request->post('sql'));
        }
        
        $date = Session::get('STATISTICS-TABLE-DATE');
        $sql = Session::get('STATISTICS-TABLE-SQL');
        $errors = [];
        $data = [];
        
        if ($date) {
            $query = \App\Http\Models\VariableChangesModel::whereVariableId($id);

            $d = Carbon::parse($date)->startOfDay();
            $query->whereBetween('change_date', [$d, $d->copy()->addDay()]);

            if ($sql) {
                $query->whereRaw('value '.$sql);
            }

            try {
                $data = $query->orderBy('id', 'asc')->get();
            } catch (\Exception $ex) {
                $errors['sql'] = $ex->getMessage();
                $data = [];
            }
        }
        
        return view('admin.statistics.table.statistics-table', [
            'id' => $id,
            'data' => $data,
        ])->withErrors($errors);
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function valueView(int $id) 
    {
        $item = \App\Http\Models\VariableChangesModel::find($id);
        
        return view('admin/statistics/table/statistics-table-value', [
            'item' => $item,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function valueDelete(int $id) 
    {
        try {
            $item = \App\Http\Models\VariableChangesModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function deleteAllVisibleValues(int $id) 
    {
        try {
            $date = Session::get('STATISTICS-TABLE-DATE');
            $sql = Session::get('STATISTICS-TABLE-SQL');
            
            if (!$date) {
                return 'ERROR';
            }
            
            $d = Carbon::parse($date)->startOfDay();
            $query = \App\Http\Models\VariableChangesModel::whereVariableId($id)
                        ->whereBetween('change_date', [$d, $d->copy()->addDay()]);
            if ($sql) {
                $query->whereRaw('value '.$sql);
            }
            
            return 'OK: '.$query->delete();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
    
}
