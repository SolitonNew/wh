<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class DeviceEvent extends Model
{
    protected $table = 'core_device_events';
    public $timestamps = false;

    /**
     * @param int $scriptID
     * @param array $deviceIDs
     * @return void
     */
    public static function createFromIds(int $scriptID, array $deviceIDs): void
    {
        foreach ($deviceIDs as $id) {
            $item = new DeviceEvent();
            $item->device_id = $id;
            $item->script_id = $scriptID;
            $item->event_type = 0;
            $item->save();
        }
    }
}
