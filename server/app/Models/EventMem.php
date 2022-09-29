<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventMem extends Model
{
    public const PLAN_LIST_CHANGE = 'PLAN_LIST_CHANGE';
    public const HUB_LIST_CHANGE = 'HUB_LIST_CHANGE';
    public const HOST_LIST_CHANGE = 'HOST_LIST_CHANGE';
    public const DEVICE_LIST_CHANGE = 'DEVICE_LIST_CHANGE';
    public const DEVICE_CHANGE_VALUE = 'DEVICE_CHANGE_VALUE';
    public const SCRIPT_LIST_CHANGE = 'SCRIPT_LIST_CHANGE';
    public const WEB_SPEECH = 'WEB_SPEECH';
    public const WEB_PLAY = 'WEB_PLAY';

    protected $table = 'core_events_mem';
    public $timestamps = false;

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    /**
     * @return int|null
     */
    public static function lastDeviceChangeID(): int|null
    {
        return self::max('id');
    }

    /**
     * @return int|null
     */
    public static function lastID(): int|null
    {
        return self::max('id');
    }

    /**
     * @param int $lastID
     * @return array
     */
    public static function getLastDeviceChanges(int $lastID): array
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
     * @param int $app_control
     * @param float $value
     * @return string
     */
    public static function decodeLogValue(int $app_control, float $value): string
    {
        $info = config('devices.app_controls.'.$app_control);

        if (count($info['values'])) {
            if (isset($info['values'][$value])) {
                return $info['values'][$value];
            } else {
                return $value;
            }
        } else {
            return $value.$info['unit'];
        }
    }

    /**
     * @param string $typ
     * @param mixed|null $data
     * @return void
     */
    public static function addEvent(string $typ, mixed $data = null): void
    {
        try {
            $event = new EventMem();
            $event->typ = $typ;
            $event->data = json_encode($data);
            $event->save();

            event(new \App\Events\AddedEventMem($event));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    /**
     * @return object|null
     */
    public function getData(): object|null
    {
        return json_decode($this->data);
    }
}
