<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class VariablesController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index(int $partID = 1) {
        $where = '';
        if ($partID > 1) {
            $ids = \App\Http\Models\PlanPartsModel::genIDsForGroupAtParent($partID);
            $where = 'and v.GROUP_ID in ('.$ids.')';
        }
        
        $sql = 'select v.ID,
                       c.NAME CONTROLLER_NAME,
                       0 TYP_NAME,
                       v.DIRECTION,
                       v.NAME,
                       v.COMM,
                       v.APP_CONTROL,
                       v.VALUE,
                       v.CHANNEL
                  from core_variables v, core_controllers c
                 where v.controller_id = c.id
                   '.$where.'
                order by v.name';
        
        $data = DB::select($sql);
        
        return view('admin.variables', [
            'partID' => $partID,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\VariablesModel::find($id);
        
        if ($request->method() == 'POST') {
            if (!$item) {
                $item = new \App\Http\Models\VariablesModel();
                
                try {
                    
                    return 'OK';
                } catch (\Exception $ex) {
                    return response()->json([
                        'error' => [$ex->errorInfo],
                    ]);
                }
            }
        } else {
            if (!$item) {
                $item = (object)[
                    'ID' => -1,
                    
                ];
            }
            
            return view('admin.variable-edit', [
                'item' => $item,
            ]);            
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) {
        $item = \App\Http\Models\VariablesModel::find($id);
        if ($item) {
            $item->delete();
            return 'OK';
        }
        
        return 'ERROR';
    }
}
