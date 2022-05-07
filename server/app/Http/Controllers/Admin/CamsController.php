<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Videcam;

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
     * @param int $id
     * @return string
     */
    public function editPost(Request $request, int $id)
    {
        return Videcam::storeFromRequest($request, $id);
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
