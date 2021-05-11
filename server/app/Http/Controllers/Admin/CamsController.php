<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CamsRequest;
use \App\Models\Videcam;

class CamsController extends Controller
{
    /**
     * This is an index route. Returns the videcam list view.
     * 
     * @return type
     */
    public function index() 
    {
        $data = Videcam::listAll();
        
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
        $item = Videcam::findOrCreate($id);
        
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
        Videcam::storeFromRequest($request);
        
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
        Videcam::deleteById($id);
        
        return 'OK';
    }
}
