<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Log;

class VariableController extends Controller
{
    /**
     * 
     * @param type $variableID
     * @return string
     */
    public function index($variableID) {
        $sql = "select p.name group_title, v.comm variable_title, v.app_control, v.group_id, v.value ".
               "  from core_variables v, plan_parts p ".
               " where v.id = $variableID ".
               "   and p.id = v.group_id";        
        $row = DB::select($sql)[0];
        
        $roomID = $row->group_id;
        $roomTitle = mb_strtoupper($row->group_title);
        $variableTitle = $row->variable_title;
        $control = \App\Http\Models\VariablesModel::decodeAppControl($row->app_control);
        $variableTitle = \App\Http\Models\VariablesModel::groupVariableName($roomTitle, mb_strtoupper($variableTitle), $control->label);

        
        switch ($control->typ) {
            case 1:
                return 'ERROR';
            case 2:
                return 'ERROR';
            case 3:
                return view('terminal.variable_3', [
                    'roomID' => $roomID,
                    'roomTitle' => $roomTitle,
                    'variableTitle' => $variableTitle,
                    'variableID' => $variableID,
                    'variableValue' => $row->value,
                    'control' => $control,
                ]);
        }
    }
    
    /**
     * 
     * @param type $lastID
     * @return type
     */
    public function variableChanges($lastID) {
        $lastID = (int)$lastID;
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
     * 
     * @param type $varID
     * @param type $varValue
     * @return string
     */
    public function variableSet($varID, $varValue) {
        $varID = (int)$varID;
        $varValue = (int)$varValue;
        
        try {
            DB::select("CALL CORE_SET_VARIABLE($varID, $varValue, -1)");
        } catch (\Exception $e) {
            Log::error($e);
        }
        
        return '';
    }
}
