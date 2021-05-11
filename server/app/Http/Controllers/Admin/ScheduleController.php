<?php

namespace App\Http\COntrollers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\ScheduleModel;

class ScheduleController extends Controller
{
    /**
     * The index route for working with schedule entries.
     * 
     * @return view
     */
    public function index() 
    {    
        $data = ScheduleModel::listAll();
        
        return view('admin.schedule.schedule', [
            'data' => $data,
        ]);
    }
    
    /**
     * The route to create or update schedule entries.
     * 
     * @param int $id
     * @return type
     */
    public function editShow(int $id)
    {
        $item = ScheduleModel::findOrCreate($id);

        return view('admin.schedule.schedule-edit', [
            'item' => $item,
        ]);        
    }
    
    /**
     * The route to create or update schedule entries.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function editPost(ScheduleRequest $request, int $id) 
    {
        ScheduleModel::storeFromRequest($request, $id);
        
        return 'OK';
    }
    
    /**
     * The route to delete schedule entries by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        ScheduleModel::deleteById($id);
        
        return 'OK';
    }
}
