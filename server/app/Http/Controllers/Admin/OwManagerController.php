<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Log;

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
            
            $row->VARIABLES = DB::select('select v.ID, v.NAME, v.CHANNEL
                                            from core_variables v 
                                           where v.ROM = "ow" 
                                             and v.OW_ID = '.$row->ID.'
                                          order by v.CHANNEL');
        }
        
        return view('admin.ow-manager.ow-manager', [
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
                   and d.ID = '.$id.'
                order by c.NAME, d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7';
        $data = DB::select($sql);
        if (count($data)) {
            $item = $data[0];
        } else {
            abort(404);
        }
        
        $item->ROM = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
            $item->ROM_1, 
            $item->ROM_2, 
            $item->ROM_3, 
            $item->ROM_4, 
            $item->ROM_5, 
            $item->ROM_6, 
            $item->ROM_7
        );
        
        $sql = 'select v.ID, v.NAME, v.CHANNEL
                  from core_variables v 
                 where v.ROM = "ow" 
                   and v.OW_ID = '.$item->ID.'
                order by v.CHANNEL';
                
        $item->VARIABLES = DB::select($sql);
        
        return view('admin.ow-manager.ow-manager-info', [
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
    
    /**
     * 
     * @return string
     */
    public function generateVarsForFreeDevs() {
        $devs = DB::select('select d.ID, d.CONTROLLER_ID, t.CHANNELS, t.COMM
                              from core_ow_devs d, core_ow_types t
                             where d.ROM_1 = t.CODE');
        
        $vars = DB::select('select OW_ID, CHANNEL from core_variables where ROM = "ow"');
        
        try {
            foreach($devs as $dev) {
                foreach (explode(',', $dev->CHANNELS) as $chan) {
                    $find = false;
                    foreach($vars as $var) {
                        if ($var->OW_ID == $dev->ID && $var->CHANNEL && $var->CHANNEL == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new \App\Http\Models\VariablesModel();
                        $item->CONTROLLER_ID = $dev->CONTROLLER_ID;
                        $item->ROM = 'ow';
                        $item->DIRECTION = 0;
                        $item->NAME = 'TEMP FOR OW';
                        $item->COMM = $dev->COMM;
                        $item->OW_ID = $dev->ID;
                        $item->CHANNEL = $chan;
                        $item->save();
                        $item->NAME = 'OW_'.$item->ID.'_'.$chan;
                        $item->save();
                        
                        Log::info($item->OW_ID);
                    }
                }
            }
            return 'OK';
        } catch (\Exception $ex) {
            Log::info($ex);
            return 'ERROR';
        }
    }
}