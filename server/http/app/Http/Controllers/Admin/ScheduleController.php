<?php

namespace App\Http\COntrollers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Lang;
use Log;

class ScheduleController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        
        $data = DB::select('select s.ID,
                                   s.COMM,
                                   s.ACTION_DATETIME,
                                   s.ACTION,
                                   s.INTERVAL_TIME_OF_DAY,
                                   s.INTERVAL_DAY_OF_TYPE,
                                   s.INTERVAL_TYPE,
                                   "" INTERVAL_TEXT,
                                   s.ENABLE
                              from core_scheduler s
                             where s.TEMP_VARIABLE_ID = 0
                            order by s.COMM');
        
        $types = Lang::get('admin/schedule.interval');
        $interval_time = Lang::get('admin/schedule.interval_time');
        $interval_day = Lang::get('admin/schedule.interval_day');
        
        foreach($data as &$row) {
            $s = $types[$row->INTERVAL_TYPE];
            $s .= ' '.$interval_time.': <b>'.$row->INTERVAL_TIME_OF_DAY.'</b>';
            
            switch($row->INTERVAL_TYPE) {
                case 0: // Каждый день
                    //
                    break;
                case 1: // Каждую неделю
                case 2: // Каждый месяц
                case 3: // Каждый год
                    $s .= ' '.$interval_day.': <b>'.$row->INTERVAL_DAY_OF_TYPE.'</b>';
                    break;
            }
            
            $row->INTERVAL_TEXT = $s;
        }
        
        return view('admin.schedule', [
            'data' => $data,
        ]);
    }
    
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\SchedulerModel::find($id);
        if ($request->method() == 'POST') {
            $typ = $request->post('INTERVAL_TYPE');
            try {
                $this->validate($request, [
                    'COMM' => 'required|string',
                    'ACTION' => 'required|string',
                    'INTERVAL_TIME_OF_DAY' => 'required|string',
                    'INTERVAL_DAY_OF_TYPE' => 'string|'.(in_array($typ, [1, 2, 3]) ? 'required' : 'nullable'),
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\SchedulerModel();
                    $item->TEMP_VARIABLE_ID = 0;
                }
                $item->COMM = $request->post('COMM');
                $item->ACTION = $request->post('ACTION');
                $item->ACTION_DATETIME = null;
                $item->INTERVAL_TIME_OF_DAY = $request->post('INTERVAL_TIME_OF_DAY');
                $item->INTERVAL_DAY_OF_TYPE = $request->post('INTERVAL_DAY_OF_TYPE');
                $item->INTERVAL_TYPE = $request->post('INTERVAL_TYPE');
                $item->ENABLE = $request->post('ENABLE');
                $item->save();
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->errorInfo],
                ]);
            }
        } else {
            if (!$item) {
                $item = (object)[
                    'ID' => -1,
                    'COMM' => '',
                    'ACTION' => '',
                    'INTERVAL_TIME_OF_DAY' => '',
                    'INTERVAL_DAY_OF_TYPE' => '',
                    'INTERVAL_TYPE' => 0,
                    'ENABLE' => 0,
                ];
            }
            return view('admin.schedule-edit', [
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
            $item = \App\Http\Models\SchedulerModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
