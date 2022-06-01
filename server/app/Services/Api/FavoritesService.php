<?php

namespace App\Services\Api;

use App\Models\Property;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class FavoritesService 
{
    /**
     * 
     * @return type
     */
    public function getData()
    {
        $ids = explode(',', Property::getWebChecks());
        
        $web_color = Property::getWebColors();
        
        $select = [
            'core_devices.*',
            DB::raw('(select p.name from plan_rooms p where id = core_devices.room_id) as group_name')
        ];
        
        $devices = DB::table('core_devices')
            ->select($select)
            ->whereIn('id', $ids)
            ->get();
        
        $result = [];
        foreach ($ids as $id) {
            foreach ($devices as $device) {
                if ($id == $device->id) {
                    $c = Device::decodeAppControl($device->app_control);
                    if (!$device->comm) {
                        $device->comm = $device->group_name;
                    }
                    $itemLabel = mb_strtoupper($device->comm);
                    $c->title = $c->label.' '.$itemLabel;

                    $result[] = (object)[
                        'data' => $device, 
                        'control' => $c
                    ];
                    break;
                }
            }
        }
        
        $varSteps = [];
        
        foreach ($result as &$device) {
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

            $varSteps[] = "{id: ".$device->data->id.", step: ".$device->control->varStep."}";
            
            if ($device->control->typ == 1) {
                $sql = "select UNIX_TIMESTAMP(v.created_at) * 1000 v_date, v.value ".
                       "  from core_events_mem v ".
                       " where v.device_id = ".$device->data->id.
                       "   and v.value <> 85 ".
                       "   and v.created_at > (select max(zz.created_at) ".
                       "                          from core_events_mem zz ".
                       "                         where zz.device_id = ".$device->data->id.") - interval 3 hour ".
                       " order by v.id ";
                
                $chartData = [];
                foreach(DB::select($sql) as $v_row) {
                    $x = $v_row->v_date;
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
        
        return $result;
    }
}
