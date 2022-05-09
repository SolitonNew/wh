<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventMem extends Model
{
    const PLAN_LIST_CHANGE = 'PLAN_LIST_CHANGE';
    const HUB_LIST_CHANGE = 'HUB_LIST_CHANGE';
    const HOST_LIST_CHANGE = 'HOST_LIST_CHANGE';
    const DEVICE_LIST_CHANGE = 'DEVICE_LIST_CHANGE';
    const DEVICE_CHANGE_VALUE = 'DEVICE_CHANGE_VALUE';
    
    protected $table = 'core_events_mem';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
    
    /**
     * 
     * @return type
     */
    static public function lastDeviceChangeID()
    {
        return self::max('id');
    }

    /**
     * 
     * @param type $lastID
     * @return type
     */
    static public function getLastDeviceChanges(int $lastID) 
    {
        if ($lastID > 0) {
            $sql = "select m.id, m.created_at, m.value, v.comm, v.app_control, m.device_id,
                           (select p.name from plan_rooms p where p.id = v.room_id) group_name
                      from core_events_mem m, core_devices v
                     where m.device_id = v.id
                       and m.id > ".$lastID."
                       and m.typ = 'DEVICE_CHANGE_VALUE'
                    order by m.id desc";
        } else {
            $sql = "select m.id, m.created_at, m.value, v.comm, v.app_control, m.device_id,
                           (select p.name from plan_rooms p where p.id = v.room_id) group_name
                      from core_events_mem m, core_devices v
                     where m.device_id = v.id
                       and m.typ = 'DEVICE_CHANGE_VALUE'
                    order by m.id desc
                    limit ".config("settings.admin_log_lines_count");
        }
        return DB::select($sql);
    }

    /**
     *
     * @param type $app_control
     * @param type $value
     * @return type
     */
    static public function decodeLogValue($app_control, $value) 
    {
        $dim = Lang::get('admin/hubs.log_app_control_dim.'.$app_control);
        if (is_array($dim)) {
            if (isset($dim[$value])) {
                return $dim[$value];
            } else {
                return $value;
            }
        } else {
            return $value.$dim;
        }
    }
    
    /**
     * 
     * @param string $typ
     * @param string $data
     */
    static public function addEvent(string $typ, string $data = null)
    {
        try {
            $rec = new EventMem();
            $rec->typ = $typ;
            $rec->data = $data;
            $rec->save();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
