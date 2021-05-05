<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CamsRequest;
use DB;

class CamsController extends Controller
{
    /**
     * This is an index route. Returns the videcam list view.
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
     * Route to create or update the videcam entries.
     * 
     * @param int $id
     * @return type
     */
    public function editShow(int $id) 
    {
        $item = \App\Http\Models\PlanVideoModel::find($id);
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
    
    /**
     * Route to create or update the videcam entries.
     * 
     * @param CamsRequest $request
     * @param int $id
     * @return string
     */
    public function editPost(CamsRequest $request, int $id)
    {
        \App\Http\Models\PlanVideoModel::storeFromRequest($request);
        return 'OK';
    }
    
    /**
     * Route to delete the videcam entries by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        \App\Http\Models\PlanVideoModel::deleteById($id);
        return 'OK';
    }
}
