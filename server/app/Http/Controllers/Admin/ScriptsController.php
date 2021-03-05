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
                         where v.ID = e.VARIABLE_ID
                           and e.SCRIPT_ID = s.ID) VAR_COUNT
                  from core_scripts s
                order by s.COMM asc";
        $list = DB::select($sql);        
        
        $item = \App\Http\Models\ScriptsModel::find($scriptID);
        
        if (!$item) {
            $first = \App\Http\Models\ScriptsModel::orderBy('COMM', 'asc')->first();
            if ($first) {
                return redirect(route('scripts', $first->ID));
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
            
            $sourceCode = $item->DATA;
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
                    'COMM' => 'required|string|unique:core_scripts,COMM,'.($id > 0 ? $id : ''),
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\ScriptsModel();
                    $item->DATA = 'pass';
                }
                $item->COMM = $request->post('COMM');
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
                    'ID' => -1,
                    'COMM' => '',
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
            $item->DATA = $request->post('DATA') ? $request->post('DATA') : 'pass';
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
                         where SCRIPT_ID = $id
                           and not VARIABLE_ID in ($ids_sql)";
                DB::delete($sql);
                
                // Добавляем новые
                $sql = "select v.ID
                          from core_variables v
                         where v.ID in ($ids_sql)
                           and not exists(select *
                                            from core_variable_events t
                                           where t.SCRIPT_ID = $id
                                             and t.VARIABLE_ID = v.ID)";
                Log::info($sql);
                foreach(DB::select($sql) as $row) {
                    $rec = new \App\Http\Models\VariableEventsModel();
                    $rec->EVENT_TYPE = 0;
                    $rec->VARIABLE_ID = $row->ID;
                    $rec->SCRIPT_ID = $id;
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
            foreach(DB::select('select VARIABLE_ID from core_variable_events where SCRIPT_ID = '.$id) as $row) {
                $data[] = $row->VARIABLE_ID;
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
            $execute = new \App\Library\Script\PhpExecute($request->post('COMMAND'));
            $res = $execute->run();
            return $res ? $res : 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
