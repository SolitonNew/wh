<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class OwManagerController extends Controller
{
    /**
     * 
     * @param int $controllerID
     * @return type
     */
    public function index(int $controllerID = null) {
        $where = '';
        if ($controllerID) {
            $where = " and d.CONTROLLER_ID = $controllerID ";
        }
        
        $sql = 'select d.ID, 
                       c.NAME CONTROLLER_NAME, 
                       "" ROM,
                       d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7,
                       t.CHANNELS,
                       t.COMM,
                       "" VARIABLES
                  from core_ow_devs d, core_ow_types t, core_controllers c
                 where d.CONTROLLER_ID = c.ID
                   and d.ROM_1 = t.CODE
                   '.$where.'
                order by c.NAME, d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7';
        $data = DB::select($sql);
        
        foreach($data as &$row) {
            $row->ROM = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
                $row->ROM_1, 
                $row->ROM_2, 
                $row->ROM_3, 
                $row->ROM_4, 
                $row->ROM_5, 
                $row->ROM_6, 
                $row->ROM_7
            );
            
            $row->VARIABLES = DB::select('select v.ID, v.NAME from core_variables v where v.ROM = "ow" and v.OW_ID = '.$row->ID);
        }
        
        return view('admin.ow-manager', [
            'controllerID' => $controllerID,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function info(int $id) {
        $item = \App\Http\Models\OwDevsModel::find($id);
        return view('admin.ow-manager-info', [
            'item' => $item,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) {
        try {
            $item = \App\Http\Models\OwDevsModel::find($id);
            $item->delete();            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
