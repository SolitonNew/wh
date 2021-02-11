<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class VariableController extends Controller
{
    public function index($variableID) {
        $sql = "select p.NAME GROUP_TITLE, v.COMM VARIABLE_TITLE, v.APP_CONTROL, v.GROUP_ID, v.VALUE ".
               "  from core_variables v, plan_parts p ".
               " where v.id = $variableID ".
               "   and p.ID = v.GROUP_ID";        
        $row = DB::select($sql)[0];
        
        $roomID = $row->GROUP_ID;
        $roomTitle = mb_strtoupper($row->GROUP_TITLE);
        $variableTitle = $row->VARIABLE_TITLE;
        $control = \App\Http\Models\VariablesModel::decodeAppControl($row->APP_CONTROL);
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
                    'variableValue' => $row->VALUE,
                    'control' => $control,
                ]);
        }
    }
    
    public function variableChanges($lastID) {
        $lastID = (int)$lastID;
        if ($lastID > 0) {
            $res = DB::select("select c.ID, c.VARIABLE_ID, c.VALUE, UNIX_TIMESTAMP(c.CHANGE_DATE) * 1000 CHANGE_DATE ".
                              "  from core_variable_changes_mem c ".
                              " where c.ID > $lastID ".
                              "   and c.VALUE <> 85 ".
                              " order by c.ID");
            return response()->json($res);
        } else {
            return 'LAST_ID: '.\App\Http\Models\VariableChangesModel::lastVariableID();
        }
    }
    
    public function variableSet($varID, $varValue) {
        $varID = (int)$varID;
        $varValue = (int)$varValue;
        
        try {
            DB::select("CALL CORE_SET_VARIABLE($varID, $varValue, -1)");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
        }
        
        return '';
    }
}
