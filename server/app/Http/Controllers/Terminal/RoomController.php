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
        
        $roomTitle = mb_strtoupper($room->name);
        
        $web_color = \App\Http\Models\PropertysModel::getWebColors();
        
        $sql = "select v.* from core_variables v " .
               " where v.group_id = $roomID " .
               "  and app_control in (1, 3, 4, 5, 7, 10, 11, 13, 14) ".
               " order by v.name";    
       
        $rows = [];
        foreach (\Illuminate\Support\Facades\DB::select($sql) as $row) {
            $c = \App\Http\Models\VariablesModel::decodeAppControl($row->app_control);
            $itemLabel = \App\Http\Models\VariablesModel::groupVariableName($roomTitle, mb_strtoupper($row->comm), mb_strtoupper($c->label));
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

            $varSteps[] = "{id: ".$row->data->id.", step: ".$row->control->varStep."}";
            
            if ($row->control->typ == 1) {
                $sql = "select UNIX_TIMESTAMP(v.change_date) * 1000 v_date, v.value ".
                       "  from core_variable_changes_mem v ".
                       " where v.variable_id = ".$row->data->id.
                       "   and v.value <> 85 ".
                       "   and v.change_date > (select max(zz.change_date) ".
                       "                          from core_variable_changes_mem zz ".
                       "                         where zz.variable_id = ".$row->data->id.") - interval 3 hour ".
                       " order by v.id ";
                
                $chartData = [];
                foreach(\Illuminate\Support\Facades\DB::select($sql) as $row) {
                    $x = $row->v_date;
                    $y = $row->value;
                    $data[] = "{x: $x, y: $y}";
                }
                
                $charts[] = (object)[
                    'id' => $row->data->id,
                    'data' => implode(', ', $chartData),
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
