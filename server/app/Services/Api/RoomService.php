<?php

namespace App\Services\Api;

use App\Models\Room;
use App\Models\Property;
use App\Models\Device;
use App\Models\CamcorderHost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoomService 
{   
    /**
     * 
     * @param int $roomID
     * @return type
     */
    public function getData(int $roomID)
    {
        $api_token = Auth::user()->api_token;
        
        $app_controls = Device::getVisibleAppControlList();
        
        $room = Room::find($roomID);
        
        $roomTitle = mb_strtoupper($room->name);
        
        $web_color = Property::getWebColors();
        
        $groupIDs = Room::genIDsForRoomAtParent($roomID);
        
        $sql = "select v.*, 
                       0 is_root,
                       (select p.name from plan_rooms p where p.id = v.room_id) group_name
                  from core_devices v 
                 where v.room_id in ($groupIDs) 
                   and app_control in (".implode(', ', $app_controls).") 
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
            
            // Chart data
            if ($device->control->typ == 1) {
                $sql = "select v.id, v.created_at, v.value ".
                       "  from core_device_changes v ".
                       " where v.device_id = ".$device->data->id.
                       "   and v.created_at > CURRENT_TIMESTAMP() - interval 3 hour".
                       " order by v.id ";
                
                $chartData = [];
                $firstID = false;
                foreach(DB::select($sql) as $v_row) {
                    if ($firstID === false) $firstID = $v_row->id;
                    $x = \Carbon\Carbon::parse($v_row->created_at, 'UTC')->toRfc2822String();
                    $y = $v_row->value;
                    $chartData[] = (object)[
                        'x' => $x,
                        'y' => $y,
                    ];
                }
                
                if ($firstID && count($chartData) < 25) {
                    $sql = "select v.created_at, v.value ".
                           "  from core_device_changes v ".
                           " where v.device_id = ".$device->data->id.
                           "   and v.created_at > CURRENT_TIMESTAMP() - interval 1 day".
                           "   and v.id < ".$firstID.
                           " order by v.id desc ".
                           " limit 1" ;
                    $firsts = DB::select($sql);
                        
                    if (count($firsts)) {
                        $x = \Carbon\Carbon::parse($firsts[0]->created_at, 'UTC')->toISOString();
                        $y = $firsts[0]->value;
                        array_unshift($chartData, (object)[
                            'x' => $x,
                            'y' => $y,
                        ]);
                    }
                }
                
                $device->chartColor = $color;
                $device->chartData = $chartData;
            }
            
            // Camcorder data
            if ($device->data->app_control == 6) {
                $cam = CamcorderHost::find($device->data->host_id);
                if ($cam && file_exists($cam->getThumbnailFileName())) {
                    $device->camcorderData = (object)[
                        'id' => $cam->id,
                    ];
                }
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
