<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Lang;
use Log;

class ScriptsController extends Controller
{
    /**
     * 
     * @param int $scriptID
     * @return type
     */
    public function index(int $scriptID = null) {
        $sql = "select s.*, 
                       (select count(*) 
                          from core_variables v, core_variable_events e
                         where v.id = e.variable_id
                           and e.script_id = s.id) var_count
                  from core_scripts s
                order by s.comm asc";
        $list = DB::select($sql);        
        
        $item = \App\Http\Models\ScriptsModel::find($scriptID);
        
        if (!$item) {
            $first = \App\Http\Models\ScriptsModel::orderBy('comm', 'asc')->first();
            if ($first) {
                return redirect(route('scripts', $first->id));
            }
        }
        
        return view('admin.scripts.scripts', [
            'scriptID' => $scriptID,
            'list' => $list,
            'data' => $item,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\ScriptsModel::find($id);
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'comm' => 'required|string|unique:core_scripts,comm,'.($id > 0 ? $id : ''),
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\ScriptsModel();
                    $item->data = '/* COMMENT */';
                }
                $item->comm = $request->post('comm');
                $item->save();
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'errors' => [$ex->getMessage()],
                ]);
            }
        } else {
            if (!$item) {
                $item = (object)[
                    'id' => -1,
                    'comm' => '',
                 ];
            }
            
            return view('admin.scripts.script-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) {
        try {            
            \App\Http\Models\VariableEventsModel::whereScriptId($id)->delete();
            $item = \App\Http\Models\ScriptsModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function saveScript(Request $request, int $id) {
        $item = \App\Http\Models\ScriptsModel::find($id);
        if ($item) {
            $item->data = $request->post('data') ? $request->post('data') : '/* COMMENT */';
            $item->save();
            return 'OK';
        } else {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return type
     */
    public function attacheEvents(Request $request, int $id) {
        if ($request->method() == 'POST') {
            try {
                $ids = $request->post('variables');
                $ids[] = 0;
                $ids_sql = implode(', ', $ids);

                // Удаляем записи которые не отмечены
                $changes = \App\Http\Models\VariableEventsModel::whereScriptId($id)
                                ->whereNotIn('variable_id', $ids)
                                ->delete();
                
                // Добавляем новые
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
                
                if ($changes) { // Шлем вручную событие изменения
                    event(new \App\Http\Events\FirmwareChangedEvent());
                }
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'errors' => [
                        $ex->getMessage()
                    ]
                ]);
            }
        } else {
            $data = [];
            foreach(DB::select('select variable_id from core_variable_events where script_id = '.$id) as $row) {
                $data[] = $row->variable_id;
            }
            
            return view('admin.scripts.script-events', [
                'id' => $id,
                'data' => $data,
            ]);
        }
    }
    
    /**
     * 
     * @param Request $request
     */
    public function scriptTest(Request $request) {
        try {
            $execute = new \App\Library\Script\PhpExecute($request->post('command'));
            $report = [];
            $res = $execute->run(true, $report);
            
            if (!$res) {
                $log = [];
                $log[] = 'Testing completed successfully';
                $log[] = str_repeat('-', 40);
                $log[] = 'FUNCTIONS ['.count($report['functions']).']';
                foreach($report['functions'] as $key => $val) {
                    $log[] = '    '.$key;
                }
                $log[] = '';
                
                $res = implode("\n", $log);
            }
            
            return $res;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
