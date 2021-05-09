<?php

namespace App\Http\Services\Terminal;

use App\Http\Models\VariablesModel;
use App\Http\Models\VariableChangesMemModel;
use DB;

class DeviceService 
{
    /**
     * 
     * @param type $deviceID
     * @return string
     */
    public function showDeviceView($deviceID)
    {
        $sql = "select p.name group_title, v.comm device_title, v.app_control, v.group_id, v.value ".
               "  from core_variables v, plan_parts p ".
               " where v.id = $deviceID ".
               "   and p.id = v.group_id";        
        $row = DB::select($sql)[0];
        
        $roomID = $row->group_id;
        $roomTitle = mb_strtoupper($row->group_title);
        $deviceTitle = $row->device_title;
        $control = VariablesModel::decodeAppControl($row->app_control);
        $deviceTitle = VariablesModel::groupVariableName($roomTitle, mb_strtoupper($deviceTitle), $control->label);

        
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
     * 
     * @param type $lastID
     * @return type
     */
    public function getChanges($lastID)
    {
        if ($lastID > 0) {
            $res = DB::select("select c.id, c.variable_id, c.value, UNIX_TIMESTAMP(c.change_date) * 1000 change_date ".
                              "  from core_variable_changes_mem c ".
                              " where c.id > $lastID ".
                              " order by c.id");
            return response()->json($res);
        } else {
            return 'LAST_ID: '.VariableChangesMemModel::lastVariableID();
        }
    }
    
    public function setValue(int $deviceID, int $value)
    {
        VariablesModel::setValue($deviceID, $value);
    }
}
