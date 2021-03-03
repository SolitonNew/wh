<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        return redirect(route('plan'));
    }
    
    /**
     * 
     * @param int $lastID
     * @return type
     */
    public function variableChanges(int $lastID) {
        \App\Http\Models\VariableChangesMemModel::setLastVariableID($lastID);
        return view('admin.log');
    }
}
