<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class VariablesController extends Controller
{
    public function index() {
        
        $sql = 'select v.ID,
                       c.NAME CONTROLLER_NAME,
                       0 TYP_NAME,
                       v.DIRECTION,
                       v.NAME,
                       v.COMM,
                       v.APP_CONTROL,
                       v.VALUE,
                       v.CHANNEL
                  from core_variables v, core_controllers c
                 where v.controller_id = c.id
                order by v.name';
        
        $data = DB::select($sql);
        
        return view('admin.variables', [
            'data' => $data,
        ]);
    }
}
