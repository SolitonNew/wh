<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Log;

class DeviceController extends Controller
{
    /**
     * This route is for the device management page.
     * 
     * @param type $deviceID
     * @return string
     */
    public function index($deviceID) 
    {
        $sql = "select p.name group_title, v.comm device_title, v.app_control, v.group_id, v.value ".
               "  from core_variables v, plan_parts p ".
               " where v.id = $deviceID ".
               "   and p.id = v.group_id";        
        $row = DB::select($sql)[0];
        
        $roomID = $row->group_id;
        $roomTitle = mb_strtoupper($row->group_title);
        $deviceTitle = $row->device_title;
        $control = \App\Http\Models\VariablesModel::decodeAppControl($row->app_control);
        $deviceTitle = \App\Http\Models\VariablesModel::groupVariableName($roomTitle, mb_strtoupper($deviceTitle), $control->label);

        
        switch ($control->typ) {
            case 1:
                return 'ERROR';
            case 2:
                return 'ERROR';
            case 3:
                return view('terminal.device_3', [
                    'roomID' => $roomID,
                    'roomTitle' => $roomTitle,
                    'deviceTitle' => $deviceTitle,
                    'deviceID' => $deviceID,
                    'deviceValue' => $row->value,
                    'control' => $control,
                ]);
        }
    }
    
    /**
     * This route return the latest device changes.
     * 
     * @param type $lastID
     * @return type
     */
    public function changes(int $lastID) 
    {
        if ($lastID > 0) {
            $res = DB::select("select c.id, c.variable_id, c.value, UNIX_TIMESTAMP(c.change_date) * 1000 change_date ".
                              "  from core_variable_changes_mem c ".
                              " where c.id > $lastID ".
                              " order by c.id");
            return response()->json($res);
        } else {
            return 'LAST_ID: '.\App\Http\Models\VariableChangesMemModel::lastVariableID();
        }
    }
    
    /**
     * This route sets the device value.
     * 
     * @param type $deviceID
     * @param type $value
     * @return string
     */
    public function set(int $deviceID, int $value) 
    {
        try {
            DB::select("CALL CORE_SET_VARIABLE($deviceID, $value, -1)");
        } catch (\Exception $e) {
            Log::error($e);
        }
        
        return '';
    }
}
