<?php

namespace App\Services\Api;

use App\Models\Room;
use App\Models\Property;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class RoomService 
{   
    /**
     * 
     * @param int $roomID
     * @return type
     */
    public function getData(int $roomID)
    {
        $room = Room::find($roomID);
        
        $roomTitle = mb_strtoupper($room->name);
        
        $web_color = Property::getWebColors();
        
        $groupIDs = Room::genIDsForRoomAtParent($roomID);
        
        $sql = "select v.*, 
                       0 is_root,
                       (select p.name from plan_rooms p where p.id = v.room_id) group_name
                  from core_devices v 
                 where v.room_id in ($groupIDs) 
                   and app_control in (1, 3, 4, 5, 7, 10, 11, 13, 14) 
                order by v.id";    
       
        $devices = [];
        foreach (DB::select($sql) as $row) {
            if (!$row->comm) {
                $row->comm = $row->group_name;
            }
            
            $row->is_root = (mb_strpos(mb_strtoupper($row->comm), $roomTitle) !== false) ? 1 : 0;
            
            $c = Device::decodeAppControl($row->app_control);
            $c->title = Device::roomDeviceName($roomTitle, mb_strtoupper($row->comm), mb_strtoupper($c->label));

            $devices[] = (object)[
                'data' => $row, 
                'control' => $c
            ];
        }
        
        foreach ($devices as &$device) {
            $itemLabel = $device->control->title;

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
            
            if ($device->control->typ == 1) {
                $sql = "select v.created_at, v.value ".
                       "  from core_device_changes v ".
                       " where v.device_id = ".$device->data->id.
                       "   and v.created_at > (select max(zz.created_at) ".
                       "                          from core_device_changes zz ".
                       "                         where zz.device_id = ".$device->data->id.") - interval 3 hour ".
                       " order by v.id ";
                
                $chartData = [];
                foreach(DB::select($sql) as $v_row) {
                    $x = \Carbon\Carbon::parse($v_row->created_at, 'UTC')->toRfc2822String();
                    $y = $v_row->value;
                    $chartData[] = (object)[
                        'x' => $x,
                        'y' => $y,
                    ];
                }
                
                $device->chartColor = $color;
                $device->chartData = $chartData;
            }
        }
        
        usort($devices, function ($item1, $item2) {
            return $item1->data->is_root < $item2->data->is_root;
        });
        
        return (object)[
            'title' => $roomTitle,
            'devices' => $devices,
        ];
    }
}
