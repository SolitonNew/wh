<?php

namespace App\Http\Controllers\Admin\Jurnal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Carbon\Carbon;
use App\Http\Services\HistoryService;
use Session;
use DB;

class HistoryController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_historyService;
    
    /**
     * 
     * @param HistoryService $historyService
     */
    public function __construct(HistoryService $historyService) 
    {
        $this->_historyService = $historyService;
    }
    
    /**
     * Index route to display device history data.
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
                return redirect(route('admin.jurnal-history', Session::get('STATISTICS-TABLE-ID')));
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
        
        $sql_devs = "select v.*,
                            (select p.name from plan_parts p where p.id = v.group_id) group_name
                       from core_variables v
                     order by v.name";
        $devices = DB::select($sql_devs);
        
        return view('admin.jurnal.history.history', [
            'id' => $id,
            'devices' => $devices,
            'data' => $data,
        ])->withErrors($errors);
    }
    
    /**
     * This route to display device history by id.
     * 
     * @param int $id
     * @return type
     */
    public function valueView(int $id) 
    {
        $item = \App\Http\Models\VariableChangesModel::find($id);
        
        return view('admin/jurnal/history/history-value', [
            'item' => $item,
        ]);
    }
    
    /**
     * This route to delete device history record by id.
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
     * This route to delete all visible device history reccords.
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
