<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VariableChangesMemModel;

class IndexController extends Controller
{
    /**
     * The index route.
     * Redirects to the hubs page.
     * 
     * @return redirect
     */
    public function index() 
    {
        return redirect(route('admin.plan'));
    }
        
    /**
     * This route is for requests for the latest device changes.
     * The result is displayed in the main window as a list.
     * 
     * @param int $lastID
     * @return view
     */
    public function variableChanges(int $lastID) 
    {
        VariableChangesMemModel::setLastVariableID($lastID);
        return view('admin.log');
    }
}
