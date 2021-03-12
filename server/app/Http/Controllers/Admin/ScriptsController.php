<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
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
        
        if ($item) {
            $words = [
                'elif' => '@KEY_2@',
                'if' => '@KEY_1@',
                'else' => '@KEY_3@',
                'for' => '@KEY_4@',
                'import' => '@KEY_5@',
                'pass' => '@KEY_6@',
                'not' => '@KEY_7@',
            ];
            
            $sourceCode = $item->data;
            $sourceCode = str_replace(' ', '&nbsp;', $sourceCode);
            
            foreach($words as $key => $val) {
                $sourceCode = str_replace($key, $val, $sourceCode);
            }
            
            foreach($words as $key => $val) {
                $sourceCode = str_replace($val, '<span class="code-keyword">'.$key.'</span>', $sourceCode);
            }
            
            $sourceCode = nl2br($sourceCode);
        } else {
            $sourceCode = '';
        }
        
        return view('admin.scripts.scripts', [
            'scriptID' => $scriptID,
            'list' => $list,
            'data' => $item,
            'sourceCode' => $sourceCode,
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
                    $item->data = 'pass';
                }
                $item->COMM = $request->post('comm');
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
            $item->DATA = $request->post('data') ? $request->post('data') : 'pass';
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
                $ids = $request->post('VARIABLES');
                $ids[] = 0;
                $ids_sql = implode(', ', $ids);

                // Удаляем записи не которые не отмечены
                $sql = "delete from core_variable_events
                         where script_id = $id
                           and not variable_id in ($ids_sql)";
                db::delete($sql);
                
                // добавляем новые
                $sql = "select v.id
                          from core_variables v
                         where v.id in ($ids_sql)
                           and not exists(select *
                                            from core_variable_events t
                                           where t.script_id = $id
                                             and t.variable_id = v.id)";
                Log::info($sql);
                foreach(DB::select($sql) as $row) {
                    $rec = new \App\Http\Models\VariableEventsModel();
                    $rec->event_type = 0;
                    $rec->variable_id = $row->id;
                    $rec->script_id = $id;
                    $rec->save();
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
            $res = $execute->run();
            return $res ? $res : 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
