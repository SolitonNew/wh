<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class DevicesController extends Controller
{
    public function index(int $hubID = null) {
        if (!\App\Http\Models\ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        $sql = 'select v.id,
                       v.typ,
                       v.direction,
                       v.name,
                       v.comm,
                       v.app_control,
                       v.value,
                       v.channel,
                       exists(select 1 from core_variable_events e where e.variable_id = v.id) with_events
                  from core_variables v
                 where v.controller_id = '.$hubID.'
                order by 2, 5';
        
        $data = DB::select($sql);
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
            'data' => $data,
        ]);
    }
    
    public function edit(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
