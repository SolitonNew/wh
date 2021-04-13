<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class HostsController extends Controller
{
    public function index(int $hubID = null) {
        if (!\App\Http\Models\ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        $sql = 'select d.id,
                       c.name controller_name, 
                       "" rom,
                       d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7,
                       t.channels,
                       t.comm,
                       "" variables,
                       d.lost
                  from core_ow_devs d left join core_ow_types t on d.rom_1 = t.code,
                       core_controllers c
                 where d.controller_id = c.id
                   and d.controller_id = "'.$hubID.'" 
                order by c.name, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7';
        $data = DB::select($sql);
        
        foreach($data as &$row) {
            $row->rom = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
                $row->rom_1, 
                $row->rom_2, 
                $row->rom_3, 
                $row->rom_4, 
                $row->rom_5, 
                $row->rom_6, 
                $row->rom_7
            );
            
            $row->devices = DB::select('select v.id, v.name, v.channel
                                          from core_variables v 
                                         where v.typ = "ow" 
                                           and v.ow_id = '.$row->id.'
                                        order by v.channel');
        }
        
        return view('admin.hubs.hosts.hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => $data,
        ]);
    }
    
    public function edit(int $nubId, int $id) {
        
    }
    
    public function delete(int $id) {
        
    }
}
