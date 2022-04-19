<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;
use Lang;
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
                return Device::whereHubId($hubID)
                            ->orderBy('name', 'asc')
                            ->get();
            case 'empty':
                return Device::whereHubId($hubID)
                            ->doesntHave('room')
                            ->orderBy('name', 'asc')
                            ->get();
            default:
                $ids = explode(',', Room::genIDsForRoomAtParent($roomID)) ?? [];
                return Device::whereHubId($hubID)
                            ->whereIn('room_id', $ids)
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
            if ($request->value !== null) {
                Device::setValue($item->id, $request->value);
            }
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
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
     * @param int $hubID
     * @return type
     */
    /*static public function hostList(int $hubID)
    {
        $OwHosts = OwHost::whereHubId($hubID)
                ->with('devices')
                ->orderBy('rom_1', 'asc')
                ->orderBy('rom_2', 'asc')
                ->orderBy('rom_3', 'asc')
                ->orderBy('rom_4', 'asc')
                ->orderBy('rom_5', 'asc')
                ->orderBy('rom_6', 'asc')
                ->orderBy('rom_7', 'asc')
                ->orderBy('rom_8', 'asc')
                ->get();
        $data = [];
        foreach ($OwHosts as $dev) {
            $data[] = (object)[
                'id' => $dev->id,
                'rom' => $dev->romAsString(),
                'count' => $dev->devices->count(),
            ];
        }
        return $data;
    }*/
    
    /**
     * 
     * @param string $typ
     * @param int $hostID
     * @return array
     */
    /*static public function hostChannelList(string $typ, int $hostID = null)
    {
        switch ($typ) {
            case 'din':
                $data = config('firmware.channels.'.config('firmware.mmcu'));
                break;
            case 'ow':
                if ($hostID) {
                    $OwHost = OwHost::find($hostID);
                    if ($OwHost) {
                        $data = $OwHost->channelsOfType();
                    } else {
                        $data = [];
                    }
                } else {
                    $data = [];
                }
                break;
            default:
                $data = [];
        }
        
        return $data;
    } */
    
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
}
