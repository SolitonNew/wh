<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;
use App\Models\DeviceEvent;
use App\Events\FirmwareChangedEvent;
use DB;

class Script extends AffectsFirmwareModel
{    
    protected $table = 'core_scripts';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'data',
    ];
    
    /**
     * 
     * @return type
     */
    static public function listAll()
    {
        $sql = "select s.*, 
                       (select count(*) 
                          from core_devices v, core_device_events e
                         where v.id = e.device_id
                           and e.script_id = s.id) var_count
                  from core_scripts s
                order by s.comm asc";
        return DB::select($sql);
    }
    
    /**
     * 
     * @param int $id
     * @return \App\Models\Script
     */
    static public function findOrCreate(int $id)
    {
        $item = Script::find($id);
        if (!$item) {
            $item = new Script();
            $item->id = -1;
            $item->data = '/* NEW SCRIPT */';
        }
        
        return $item;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    static public function storeFromRequest(Request $request, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'comm' => 'required|string|unique:core_scripts,comm,'.($id > 0 ? $id : ''),
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // Saving -----------------------
        try {
            $item = Script::find($id);
            if (!$item) {
                $item = new Script();
                $item->data = '/* NEW SCRIPT */';
            }
            $item->comm = $request->comm;
            $item->save();
            
            // Store event
            EventMem::addEvent(EventMem::SCRIPT_LIST_CHANGE, [
                'id' => $item->id,
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
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {            
            DeviceEvent::whereScriptId($id)->delete();
            $item = Script::find($id);
            $item->delete();
            
            // Store event
            EventMem::addEvent(EventMem::SCRIPT_LIST_CHANGE, [
                'id' => $item->id,
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
     * 
     * @param Request $request
     * @param int $id
     */
    static public function storeDataFromRequest(Request $request, int $id)
    {
        $item = Script::find($id);
        try {
            $item->data = $request->data ?? '/* NEW SCRIPT */';
            $item->save();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    static public function attachedDevicesIds(int $id)
    {
        $sql = 'select device_id 
                  from core_device_events 
                 where script_id = '.$id;
        
        $data = [];
        foreach(DB::select($sql) as $row) {
            $data[] = $row->device_id;
        }
        return $data;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     */
    static public function attachDevicesFromRequest(Request $request, int $id)
    {
        try {
            $ids = $request->variables;
            $ids[] = 0;
            $ids_sql = implode(', ', $ids);

            // Delete old not checked records
            $changes = DeviceEvent::whereScriptId($id)
                            ->whereNotIn('device_id', $ids)
                            ->delete();

            // Add new records
            $sql = "select v.id
                      from core_devices v
                     where v.id in ($ids_sql)
                       and not exists(select *
                                        from core_device_events t
                                       where t.script_id = $id
                                         and t.device_id = v.id)";
            foreach(DB::select($sql) as $row) {
                $rec = new DeviceEvent();
                $rec->event_type = 0;
                $rec->device_id = $row->id;
                $rec->script_id = $id;
                $rec->save();
                $changes++;
            }

            if ($changes) {
                event(new FirmwareChangedEvent());
            }
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
