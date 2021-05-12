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
        'ow_id',
        'direction',
        'name',
        'channel',
    ];
    
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
    
    static public function devicesList(int $hubID, $groupID)
    {
        $where = '';
        switch ($groupID) {
            case 'none':
                break;
            case 'empty':
                $where = ' and not exists(select 1 from plan_rooms pp where v.room_id = pp.id)';
                break;
            default:
                $groupID = (int)$groupID;
                $ids = Room::genIDsForRoomAtParent($groupID);
                if ($ids) {
                    $where = ' and v.room_id in ('.$ids.') ';
                }
                break;
        }
        
        $sql = 'select v.id,
                       v.typ,
                       v.name,
                       v.comm,
                       v.app_control,
                       v.value,
                       v.channel,
                       v.last_update,
                       (select p.name from plan_rooms p where p.id = v.room_id) group_name,
                       exists(select 1 from core_device_events e where e.device_id = v.id) with_events
                  from core_devices v
                 where v.hub_id = '.$hubID.'
                '.$where.'
                order by v.name';
        
        return DB::select($sql);
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
            $item->ow_id = $request->typ == 'ow' ? $request->ow_id : null;
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
    static public function hostList(int $hubID)
    {
        $sql = "select d.id, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7, d.rom_8,
                       (select count(1)
                          from core_devices v 
                         where v.ow_id = d.id) num
                  from core_ow_devs d
                 where d.hub_id = $hubID
                order by d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7, d.rom_8";
        return DB::select($sql);
    }
    
    /**
     * 
     * @param string $typ
     * @param int $hostID
     * @return array
     */
    static public function hostChannelList(string $typ, int $hostID = null)
    {
        switch ($typ) {
            case 'din':
                $data = config('firmware.channels.'.config('firmware.mmcu'));
                break;
            case 'ow':
                if ($hostID) {
                    $owDev = OwDev::find($hostID);
                    if ($owDev) {
                        $data = $owDev->channelsOfType();
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
    }
    
    /**
     * 
     * @return type
     */
    static public function devicesListWithRoomName()
    {
        $sql = "select v.*,
                       (select p.name from plan_rooms p where p.id = v.room_id) group_name
                  from core_devices v
                order by v.name";
        
        return DB::select($sql);
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
}
