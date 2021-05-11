<?php

namespace App\Http\Services\Terminal;

use App\Models\Room;
use App\Models\Property;
use App\Models\Device;
use DB;

class RoomService 
{   
    /**
     * 
     * @param int $roomID
     * @return type
     */
    public function roomData(int $roomID)
    {
        $room = Room::find($roomID);
        
        $roomTitle = mb_strtoupper($room->name);
        
        $web_color = Property::getWebColors();
        
        $groupIDs = Room::genIDsForGroupAtParent($roomID);
        
        $sql = "select v.*, 
                       0 is_root,
                       (select p.name from plan_parts p where p.id = v.group_id) group_name
                  from core_variables v 
                 where v.group_id in ($groupIDs) 
                   and app_control in (1, 3, 4, 5, 7, 10, 11, 13, 14) 
                order by v.id";    
       
        $rows = [];
        foreach (DB::select($sql) as $row) {
            if (!$row->comm) {
                $row->comm = $row->group_name;
            }
            
            $row->is_root = (mb_strpos(mb_strtoupper($row->comm), $roomTitle) !== false) ? 1 : 0;
            
            $c = Device::decodeAppControl($row->app_control);
            $c->title = Device::groupVariableName($roomTitle, mb_strtoupper($row->comm), mb_strtoupper($c->label));

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
                foreach(DB::select($sql) as $v_row) {
                    $x = $v_row->v_date;
                    $y = $v_row->value;
                    $chartData[] = "{x: $x, y: $y}";
                }
                
                $charts[] = (object)[
                    'id' => $row->data->id,
                    'data' => implode(', ', $chartData),
                    'color' => $color,
                ];
            }
        }
        
        usort($rows, function ($item1, $item2) {
            return $item1->data->is_root < $item2->data->is_root;
        });
        
        return [
            $roomTitle,
            $rows,
            $charts,
            implode(', ', $varSteps),
        ];
    }
}