<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;
use \Illuminate\Http\Request;
use DB;

class ScriptsModel extends AffectsFirmwareModel
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
                          from core_variables v, core_variable_events e
                         where v.id = e.variable_id
                           and e.script_id = s.id) var_count
                  from core_scripts s
                order by s.comm asc";
        return DB::select($sql);
    }
    
    /**
     * 
     * @param int $id
     * @return \App\Http\Models\ScriptsModel
     */
    static public function findOrCreate(int $id)
    {
        $item = ScriptsModel::find($id);
        if (!$item) {
            $item = new ScriptsModel();
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
        try {
            $item = ScriptsModel::find($id);
            if (!$item) {
                $item = new \App\Http\Models\ScriptsModel();
                $item->data = '/* NEW SCRIPT */';
            }
            $item->comm = $request->comm;
            $item->save();
            return 'OK';
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
            VariableEventsModel::whereScriptId($id)->delete();
            $item = ScriptsModel::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(result()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     */
    static public function storeDataFromRequest(Request $request, int $id)
    {
        $item = \App\Http\Models\ScriptsModel::find($id);
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
        $sql = 'select variable_id 
                  from core_variable_events 
                 where script_id = '.$id;
        
        $data = [];
        foreach(DB::select($sql) as $row) {
            $data[] = $row->variable_id;
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
            $changes = \App\Http\Models\VariableEventsModel::whereScriptId($id)
                            ->whereNotIn('variable_id', $ids)
                            ->delete();

            // Add new records
            $sql = "select v.id
                      from core_variables v
                     where v.id in ($ids_sql)
                       and not exists(select *
                                        from core_variable_events t
                                       where t.script_id = $id
                                         and t.variable_id = v.id)";
            foreach(DB::select($sql) as $row) {
                $rec = new \App\Http\Models\VariableEventsModel();
                $rec->event_type = 0;
                $rec->variable_id = $row->id;
                $rec->script_id = $id;
                $rec->save();
                $changes++;
            }

            if ($changes) {
                event(new \App\Http\Events\FirmwareChangedEvent());
            }
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
