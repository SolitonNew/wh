<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Device extends AffectsFirmwareModel
{
    protected $table = 'core_devices';
    public $timestamps = false;

    /**
     * @var array|string[]
     */
    protected array $affectFirmwareFields = [
        'hub_id',
        'typ',
        'host_id',
        'direction',
        'name',
        'channel',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function events()
    {
        return $this->belongsToMany(Script::class, DeviceEvent::class, 'device_id', 'script_id');
    }

    /**
     * Makes all the necessary attributes to create a device label.
     *
     * @param int $app_control
     * @return object
     */
    public static function decodeAppControl(int $app_control): object
    {
        $info = config('devices.app_controls.'.$app_control);

        return (object)[
            'label' => $info['log'],
            'typ' => $info['typ'],
            'resolution' => $info['unit'],
            'varMin' => $info['min'],
            'varMax' => $info['max'],
            'varStep' => $info['step']
        ];
    }

    /**
     * @return array
     */
    public static function getVisibleAppControlList(): array
    {
        $result = [];
        foreach (config('devices.app_controls') as $key => $item) {
            if ($item['visible']) {
                $result[] = $key;
            }
        }
        return $result;
    }

    /**
     * @param string $groupName
     * @param string $variableName
     * @param string $appControlLabel
     * @return string
     */
    public static function roomDeviceName(string $groupName, string $variableName, string $appControlLabel): string
    {
        $resLabel = '';
        if ($appControlLabel != '') {
            $resLabel = $appControlLabel.' ';
        }
        return $resLabel.mb_strtoupper(str_replace($groupName, '', $variableName));
    }

    /**
     * Sets the device value using a stored procedure.
     *
     * @param int $deviceID
     * @param float $value
     * @param int $fromID
     * @return void
     */
    public static function setValue(int $deviceID, float $value, int $fromID = 0): void
    {
        try {
            DB::select("CALL CORE_SET_DEVICE($deviceID, $value, $fromID)");
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @param int $hubID
     * @param string|null $roomID
     * @return Collection
     */
    public static function devicesList(int $hubID, string|null $roomID): Collection
    {
        switch ($roomID) {
            case 'none':
                return Device::select([DB::raw('0 freedevice'), 'core_devices.*'])
                            ->whereHubId($hubID)
                            ->union(
                                Device::select([DB::raw('1 freedevice'), 'core_devices.*'])->whereNotExists(function ($query) {
                                    $query->from('core_hubs')
                                        ->whereRaw('core_devices.hub_id = core_hubs.id');
                                })
                            )
                            ->orderBy('name', 'asc')
                            ->get();
            case 'empty':
                return Device::select([DB::raw('0 freedevice'), 'core_devices.*'])
                            ->whereHubId($hubID)
                            ->doesntHave('room')
                            ->union(
                                Device::select([DB::raw('1 freedevice'), 'core_devices.*'])->whereNotExists(function ($query) {
                                    $query->from('core_hubs')
                                        ->whereRaw('core_devices.hub_id = core_hubs.id');
                                })
                                ->doesntHave('room')
                            )
                            ->orderBy('name', 'asc')
                            ->get();
            default:
                $ids = explode(',', Room::genIDsForRoomAtParent($roomID)) ?? [];
                return Device::select([DB::raw('0 freedevice'), 'core_devices.*'])
                            ->whereHubId($hubID)
                            ->whereIn('room_id', $ids)
                            ->union(
                                Device::select([DB::raw('1 freedevice'), 'core_devices.*'])->whereNotExists(function ($query) use ($ids) {
                                    $query->from('core_hubs')
                                        ->whereRaw('core_devices.hub_id = core_hubs.id');
                                })
                                ->whereIn('room_id', $ids)
                            )
                            ->orderBy('name', 'asc')
                            ->get();
        }
    }

    /**
     * @param int $id
     * @param int $hubId
     * @return Device
     */
    public static function findOrCreate(int $id, int $hubId = -1): Device
    {
        $item = Device::find($id);
        if (!$item) {
            $item = new Device();
            $item->id = -1;
            $item->hub_id = $hubId;
            $item->position = '';
        }

        return $item;
    }

    /**
     * @param Request $request
     * @param int $hubId
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function storeFromRequest(Request $request, int $hubId, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'hub_id' => 'required|numeric',
            'name' => 'required|string|unique:core_devices,name,'.($id > 0 ? $id : ''),
            'comm' => 'nullable|string',
            'host_id' => ($request->typ === 'ow' ? 'required|numeric' : ''),
            'value' => 'nullable|numeric',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Saving -----------------------
        try {
            $item = Device::find($id);
            if (!$item) {
                $item = new Device();
            }

            $item->hub_id = $request->hub_id;
            $item->typ = $request->typ;
            $item->host_id = in_array($request->typ, ['ow', 'extapi', 'i2c', 'camcorder']) ? $request->host_id : null;
            $item->name = $request->name;
            $item->comm = $request->comm;
            $item->channel = $request->channel ?? 0;
            $item->app_control = $request->app_control;
            $item->save();

            if (strlen($request->value)) {
                Device::setValue($item->id, $request->value);
            }

            // Store event
            EventMem::addEvent(EventMem::DEVICE_LIST_CHANGE, [
                'id' => $item->id,
                'hubID' => $item->hub_id,
            ]);
            // ------------

            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function deleteById(int $id)
    {
        try {
            $item = Device::find($id);
            if (!$item) abort(404);

            // Clear relations
            //DeviceChange::whereDeviceId($id)->delete();
            EventMem::whereDeviceId($id)->delete();
            DeviceEvent::whereDeviceId($id)->delete();
            // ----------------------

            $item->delete();

            // Store event
            EventMem::addEvent(EventMem::DEVICE_LIST_CHANGE, [
                'id' => $item->id,
                'hubID' => $item->hub_id,
            ]);
            // ------------
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param object|null $defaults
     * @return object|null
     */
    public function getPosition(object|null $defaults = null): object|null
    {
        $position = $this->position ? json_decode($this->position) : (object)[];

        if (!isset($position->surface)) $position->surface = ($defaults && isset($defaults->surface)) ? $defaults->surface : 'top';
        if (!isset($position->offset)) $position->offset = ($defaults && isset($defaults->offset)) ? $defaults->offset : 0;
        if (!isset($position->cross)) $position->cross = ($defaults && isset($defaults->cross)) ? $defaults->cross : 0;

        return $position;
    }

    /**
     * @param int $deviceID
     * @param int $afterHours
     * @return float
     */
    public static function getValue(int $deviceID, int $afterHours = 0): float
    {
        $device = self::find($deviceID);

        if (!$device) return 0;

        // Return valur for now
        if ($afterHours == 0) {
            return $device->value;
        }

        if ($device->typ != 'extapi') return 0;

        $host = ExtApiHost::find($device->host_id);

        if (!$host) return 0;

        $driver = $host->driver();

        if (!$driver) return 0;

        return $driver->getForecastValue($device->channel, $afterHours);
    }

    /**
     * @return Collection
     */
    public static function getForecastSortList(): Collection
    {
        return self::whereTyp('extapi')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param array $app_control
     * @return Collection
     */
    public static function getDeviceListByAppControl(array $app_control): Collection
    {
        return self::with('room')
            ->whereIn('app_control', $app_control)
            ->orderBy('name')
            ->get();
    }
}
