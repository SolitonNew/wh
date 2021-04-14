<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Log;

class CamsController extends Controller
{
    /**
     * Индексный маршрут. 
     * Возвращает вьюху со списком камер.
     * 
     * @return type
     */
    public function index() 
    {
        $sql = 'select c.*,
                       v.name var_name
                  from plan_video c
                left join core_variables v on c.alert_var_id = v.id
                order by c.name';
        
        $data = DB::select($sql);
        
        return view('admin.cams.cams', [
            'data' => $data,
        ]);
    }
    
    /**
     * Маршрут создать/изменить запись камеры.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) 
    {
        $item = \App\Http\Models\PlanVideoModel::find($id);
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'name' => 'required|string|unique:plan_video,name,'.($id > 0 ? $id : ''),
                    'url' => 'required|string',
                    'url_low' => 'required|string',
                    'url_high' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\PlanVideoModel();
                }
                
                $item->name = $request->post('name');
                $item->url = $request->post('url');
                $item->url_low = $request->post('url_low');
                $item->url_high = $request->post('url_high');
                $item->alert_var_id = $request->post('alert_var_id');
                $item->save();
                if ($id == -1) {
                    $item->order_num = $item->id;
                    $item->save();
                }
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
                    'name' => '',
                    'url' => '',
                    'url_low' => '',
                    'url_high' => '',
                    'alert_var_id' => -1,
                ];
            }
            return view('admin.cams.cam-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * Маршурт для удаления записи камеры.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        try {
            $item = \App\Http\Models\PlanVideoModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
