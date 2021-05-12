<?php

namespace App\Http\COntrollers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * The index route for working with schedule entries.
     * 
     * @return view
     */
    public function index() 
    {    
        $data = Schedule::listAll();
        
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
        $item = Schedule::findOrCreate($id);

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
        Schedule::storeFromRequest($request, $id);
        
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
        Schedule::deleteById($id);
        
        return 'OK';
    }
}
