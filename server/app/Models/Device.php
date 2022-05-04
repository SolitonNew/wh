<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Log;
use DB;

class Device extends AffectsFirmwareModel
{    
    protected $table = 'core_devices';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'hub_id',
        'typ',
        'host_id',
        'direction',
        'name',
        'channel',
    ];
    
    /**
     * 
     * @return type
     */
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
     * @param type $app_control
     * @return object
     */
    static public function decodeAppControl($app_control) 
    {
        $control = '';
        $typ = -1; // 1-label; 2-switch; 3-track;
        $resolution = '';
        $varMin = 0;
        $varMax = 10;
        $varStep = 1;    
        switch ($app_control) {
            case 1: // Light
                $control = Lang::get('admin/hubs.log_app_control.1');
                $typ = 2;
                break;
            case 3: // Socket
                $control = '';
                $typ = 2;
                break;
            case 4: // Termometr
                $control = Lang::get('admin/hubs.log_app_control.4');
                $typ = 1;
                $resolution = '°C';
                break;
            case 5: // Termostat
                $control = Lang::get('admin/hubs.log_app_control.5');
                $typ = 3;
                $resolution = '°C';
                $varMin = 15;
                $varMax = 30;
                $varStep = 1;
                break;
            case 7: // Fan
                $control = Lang::get('admin/hubs.log_app_control.7');
                $typ = 3;
                $resolution = '%';
                $varMin = 0;
                $varMax = 100;
                $varStep = 10;
                break;
            case 10: // Humidity sensor
                $control = Lang::get('admin/hubs.log_app_control.10');
                $typ = 1;
                $resolution = '%';
                break;
            case 11: // Gas sensor
                $control = Lang::get('admin/hubs.log_app_control.11');
                $typ = 1;
                $resolution = 'ppm';
                break;
            case 13: // Atm. pressure
                $control = '';
                $typ = 1;
                $resolution = 'mm';
                break;
            case 14: // Current sensor
                $control = Lang::get('admin/hubs.log_app_control.14');
                $typ = 1;
                $resolution = 'A';
                break;
            case 15: // Speed
                $control = Lang::get('admin/hubs.log_app_control.15');
                $typ = 1;
                $resolution = 'm/s';
                break;
            case 16: // Direction
                $control = Lang::get('admin/hubs.log_app_control.16');
                $typ = 1;
                $resolution = '°';
                break;
        }

        return (object)[
            'label' => $control,
            'typ' => $typ,
            'resolution' => $resolution,
            'varMin' => $varMin,
            'varMax' => $varMax,
            'varStep' => $varStep
        ];
    }
    
    /**
     * 
     * 
     * @param type $groupName
     * @param type $variableName
     * @param type $appControlLabel
     * @return string
     */
    static public function roomDeviceName($groupName, $variableName, $appControlLabel) 
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
     */
    static public function setValue(int $deviceID, float $value)
    {
        try {
            DB::select("CALL CORE_SET_DEVICE($deviceID, $value, -1)");
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
    
    static public function devicesList(int $hubID, $roomID)
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
     * 
     * @param int $id
     * @param int $hubId
     * @return \App\Models\Device
     */
    static public function findOrCreate(int $id, int $hubId = -1)
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
     * 
     * @param Request $request
     * @param int $hubId
     * @param int $id
     */
    static public function storeFromRequest(Request $request, int $hubId, int $id)
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
            $item->host_id = in_array($request->typ, ['ow', 'software']) ? $request->host_id : null;
            $item->name = $request->name;
            $item->comm = $request->comm;
            $item->channel = $request->channel ?? 0;
            $item->app_control = $request->app_control;
            $item->save();
            
            if (strlen($request->value)) {
                Device::setValue($item->id, $request->value);
            }
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }
    
    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {
            $item = Device::find($id);
            if (!$item) abort(404);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param type $defaults
     * @return type
     */
    public function getPosition($defaults = null)
    {
        $position = $this->position ? json_decode($this->position) : (object)[];
        
        if (!isset($position->surface)) $position->surface = ($defaults && isset($defaults->surface)) ? $defaults->surface : 'top';
        if (!isset($position->offset)) $position->offset = ($defaults && isset($defaults->offset)) ? $defaults->offset : 0;
        if (!isset($position->cross)) $position->cross = ($defaults && isset($defaults->cross)) ? $defaults->cross : 0;
        
        return $position;
    }
    
    /**
     * 
     * @param int $deviceID
     * @param int $afterHours
     * @return int
     */
    static public function getValue(int $deviceID, int $afterHours = 0)
    {
        $device = self::find($deviceID);
        
        if (!$device) return 0;
        
        // Return valur for now
        if ($afterHours == 0) {
            return $device->value;
        }

        if ($device->typ != 'software') return 0;

        $host = SoftHost::find($device->host_id);
        
        if (!$host) return 0;
        
        $driver = $host->driver();
        
        if (!$driver) return 0;
        
        return $driver->getForecastValue($device->channel, $afterHours);
    }
    
    /**
     * 
     * @return type
     */
    static public function getForecastSortList()
    {
        return self::whereTyp('software')
            ->orderBy('name')
            ->get();
    }
}
