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
     * The index route for working with schedule entries.
     * 
     * @return view
     */
    public function index() 
    {    
        $data = DB::select('select s.id,
                                   s.comm,
                                   s.action_datetime,
                                   s.action,
                                   s.interval_time_of_day,
                                   s.interval_day_of_type,
                                   s.interval_type,
                                   "" interval_text,
                                   s.enable
                              from core_schedule s
                             where s.temp_variable_id = 0
                            order by s.comm');
        
        $types = Lang::get('admin/schedule.interval');
        $interval_time = Lang::get('admin/schedule.interval_time');
        $interval_day = Lang::get('admin/schedule.interval_day');
        
        foreach($data as &$row) {
            $s = $types[$row->interval_type];
            $s .= ' '.$interval_time.': <b>'.$row->interval_time_of_day.'</b>';
            
            switch($row->interval_type) {
                case 0: // Каждый день
                    //
                    break;
                case 1: // Каждую неделю
                case 2: // Каждый месяц
                case 3: // Каждый год
                    $s .= ' '.$interval_day.': <b>'.$row->interval_day_of_type.'</b>';
                    break;
            }
            
            $row->interval_text = $s;
        }
        
        return view('admin.schedule.schedule', [
            'data' => $data,
        ]);
    }
    
    /**
     * The route to create or update schedule entries.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) 
    {
        $item = \App\Http\Models\ScheduleModel::find($id);
        if ($request->method() == 'POST') {
            $typ = $request->post('interval_type');
            try {
                $this->validate($request, [
                    'comm' => 'required|string',
                    'action' => 'required|string',
                    'interval_time_of_day' => 'required|string',
                    'interval_day_of_type' => 'string|'.(in_array($typ, [1, 2, 3]) ? 'required' : 'nullable'),
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\ScheduleModel();
                    $item->temp_variable_id = 0;
                }
                $item->comm = $request->post('comm');
                $item->action = $request->post('action');
                $item->action_datetime = null;
                $item->interval_time_of_day = $request->post('interval_time_of_day');
                $item->interval_day_of_type = $request->post('interval_day_of_type');
                $item->interval_type = $request->post('interval_type');
                $item->enable = $request->post('enable');
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
                    'id' => -1,
                    'comm' => '',
                    'action' => '',
                    'interval_time_of_day' => '',
                    'interval_day_of_type' => '',
                    'interval_type' => 0,
                    'enable' => 0,
                ];
            }
            return view('admin.schedule.schedule-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * The route to delete schedule entries by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        try {
            $item = \App\Http\Models\ScheduleModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
