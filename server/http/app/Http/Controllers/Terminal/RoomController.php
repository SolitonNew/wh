<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    /**
     * 
     * @param type $roomID
     * @return type
     */
    public function index($roomID) {
        $roomID = (int)$roomID;
        
        $room = \App\Http\Models\PlanPartsModel::find($roomID);
        
        $roomTitle = mb_strtoupper($room->NAME);
        
        $web_color = \App\Http\Models\PropertysModel::getWebColors();
        
        $sql = "select v.* from core_variables v " .
               " where v.GROUP_ID = $roomID " .
               "  and APP_CONTROL in (1, 3, 4, 5, 7, 10, 11, 13, 14) ".
               " order by v.NAME";    
       
        $rows = [];
        foreach (\Illuminate\Support\Facades\DB::select($sql) as $row) {
            $c = \App\Http\Models\VariablesModel::decodeAppControl($row->APP_CONTROL);
            $itemLabel = \App\Http\Models\VariablesModel::groupVariableName($roomTitle, mb_strtoupper($row->COMM), mb_strtoupper($c->label));
            $c->title = $itemLabel;

            $rows[] = (object)[
                'data' => $row, 
                'control' => $c
            ];
        }
        
        $charts = [];
        $varSteps = [];
        
        foreach ($rows as $row) {
            $itemLabel = $row->control->title;

            $color = '';
            for ($i = 0; $i < count($web_color); $i++) {
                if (mb_strpos(mb_strtoupper($itemLabel), mb_strtoupper($web_color[$i]['keyword'])) !== false) {
                    $color = $web_color[$i]['color'];
                    if ($color) {
                        $color = "'$color'";
                    }
                    break;
                }
            }

            $varSteps[] = "{id: ".$row->data->ID.", step: ".$row->control->varStep."}";
            
            if ($row->control->typ == 1) {
                $sql = "select UNIX_TIMESTAMP(v.CHANGE_DATE) * 1000 V_DATE, v.VALUE ".
                       "  from core_variable_changes_mem v ".
                       " where v.VARIABLE_ID = ".$row->data->ID.
                       "   and v.VALUE <> 85 ".
                       "   and v.CHANGE_DATE > (select max(zz.CHANGE_DATE) ".
                       "                          from core_variable_changes_mem zz ".
                       "                         where zz.VARIABLE_ID = ".$row->data->ID.") - interval 3 hour ".
                       " order by v.ID ";
                
                $chartData = [];
                foreach(\Illuminate\Support\Facades\DB::select($sql) as $row) {
                    $x = $row->V_DATE;
                    $y = $row->VALUE;
                    $data[] = "{x: $x, y: $y}";
                }
                
                $charts[] = (object)[
                    'ID' => $row->data->ID,
                    'data' => implode($chartData, ', '),
                    'color' => $color,
                ];
            }
        }
        
        return view('terminal.room', [
            'roomTitle' => $roomTitle,
            'rows' => $rows,
            'charts' => $charts,
            'varSteps' => implode(', ', $varSteps),
        ]);
    }
}
