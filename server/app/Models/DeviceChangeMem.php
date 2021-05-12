<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Lang;

class DeviceChangeMem extends Model
{
    protected $table = 'core_device_changes_mem';
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
     * @var type
     */
    static private $_lastDeviceChangeID = -1;

    /**
     *
     * @return type
     */
    static public function lastDeviceChangeID() 
    {
        if (self::$_lastDeviceChangeID == -1) {
            self::$_lastDeviceChangeID = DeviceChangeMem::max('id') ?? -1;
        }
        return self::$_lastDeviceChangeID;
    }

    /**
     *
     * @param type $id
     */
    static public function setLastDeviceChangeID($id) 
    {
        self::$_lastDeviceChangeID = $id;
    }

    /**
     *
     * @param type $lastID
     * @param type $count
     * @return type
     */
    static public function getLastDeviceChanges() 
    {
        if (self::$_lastDeviceChangeID > 0) {
            $sql = 'select m.id, m.change_date, m.value, v.comm, v.app_control, m.device_id,
                           (select p.name from plan_rooms p where p.id = v.room_id) group_name
                      from core_device_changes_mem m, core_devices v
                     where m.device_id = v.id
                       and m.id > '.self::$_lastDeviceChangeID.'
                    order by m.id desc';
        } else {
            $sql = 'select m.id, m.change_date, m.value, v.comm, v.app_control, m.device_id,
                           (select p.name from plan_rooms p where p.id = v.room_id) group_name
                      from core_device_changes_mem m, core_devices v
                     where m.device_id = v.id
                    order by m.id desc
                    limit '.config('app.admin_log_lines_count');
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
}
