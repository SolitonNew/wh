<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\DB;
use App\Models\Device;
use App\Models\Property;

class SettingsService
{
    /**
     * @return array
     */
    public function getAllDevices(): array
    {
        $sql = "select v.*,
                       (select p.name from plan_rooms p where p.id = v.room_id) group_name
                  from core_devices v
                order by v.id";

        $devices = [];
        foreach (DB::select($sql) as $row) {
            if (!$row->comm) {
                $row->comm = $row->group_name;
            }

            $c = Device::decodeAppControl($row->app_control);
            $c->title = Device::roomDeviceName('', mb_strtoupper($row->comm), mb_strtoupper($c->label));

            $devices[] = (object)[
                'data' => $row,
                'control' => $c
            ];
        }

        return $devices;
    }

    /**
     * @param int $deviceID
     * @return void
     */
    public function addDeviceToFavorites(int $deviceID): void
    {
        $s = Property::getWebChecks();
        $checks = $s ? explode(',', $s) : [];

        if (!in_array($deviceID, $checks)) {
            $checks[] = $deviceID;
            Property::setWebChecks(implode(',', $checks));
        }
    }

    /**
     * @param int $deviceID
     * @return void
     */
    public function delDeviceFromFavorites(int $deviceID): void
    {
        $checks = explode(',', Property::getWebChecks());

        $i = array_search($deviceID, $checks);
        if ($i !== false) {
            unset($checks[$i]);
            Property::setWebChecks(implode(',', $checks));
        }
    }

    /**
     * @return array
     */
    public function getOrderList(): array
    {
        $c = Property::getWebChecks();

        $checks = $c ? explode(',', $c) : [];

        if (count($checks) == 0) return [];

        $sql = "select v.*,
                       (select p.name from plan_rooms p where p.id = v.room_id) group_name
                  from core_devices v
                 where v.id in (".implode(', ', $checks).")
                order by v.id";

        $orders = array_flip($checks);

        $devices = [];
        foreach (DB::select($sql) as $row) {
            if (!isset($orders[$row->id])) continue;

            if (!$row->comm) {
                $row->comm = $row->group_name;
            }

            $c = Device::decodeAppControl($row->app_control);
            $c->title = Device::roomDeviceName('', mb_strtoupper($row->comm), mb_strtoupper($c->label));

            $devices[] = (object)[
                'orderIndex' => $orders[$row->id],
                'data' => $row,
                'control' => $c,
            ];
        }

        usort($devices, function ($item1, $item2) {
            return $item1->orderIndex > $item2->orderIndex;
        });

        return $devices;
    }
}
