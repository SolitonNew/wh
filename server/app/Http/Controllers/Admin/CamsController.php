<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Log;

class CamsController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        $sql = 'select c.*,
                       v.NAME VAR_NAME
                  from plan_video c
                left join core_variables v on c.ALERT_VAR_ID = v.ID
                order by c.NAME';
        
        $data = DB::select($sql);
        
        return view('admin.cams', [
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\PlanVideoModel::find($id);
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'NAME' => 'required|string|unique:plan_video,NAME,'.($id > 0 ? $id : ''),
                    'URL' => 'required|string',
                    'URL_LOW' => 'required|string',
                    'URL_HIGH' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\PlanVideoModel();
                }
                
                $item->NAME = $request->post('NAME');
                $item->URL = $request->post('URL');
                $item->URL_LOW = $request->post('URL_LOW');
                $item->URL_HIGH = $request->post('URL_HIGH');
                $item->ALERT_VAR_ID = $request->post('ALERT_VAR_ID');
                $item->save();
                if ($id == -1) {
                    $item->ORDER_NUM = $item->ID;
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
                    'ID' => -1,
                    'NAME' => '',
                    'URL' => '',
                    'URL_LOW' => '',
                    'URL_HIGH' => '',
                    'ALERT_VAR_ID' => -1,
                ];
            }
            return view('admin.cam-edit', [
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
            $item = \App\Http\Models\PlanVideoModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
