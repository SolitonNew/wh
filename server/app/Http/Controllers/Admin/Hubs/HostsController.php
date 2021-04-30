<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Session;

class HostsController extends Controller
{
    /**
     * This is an index route for displaying a list of host.
     * If the hub id does not exists, redirect to the owner route.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) 
    {
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
        
        Session::put('HUB_INDEX_ID', $hubID);
        
        return view('admin.hubs.hosts.hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => $data,
        ]);
    }
    
    /**
     * Route to create or update host propertys.
     * 
     * @param Request $request
     * @param int $nubId
     * @param int $id
     * @return type
     */
    public function edit(Request $request, int $nubId, int $id)
    {
        $sql = 'select d.id, 
                       c.name controller_name, 
                       "" rom,
                       d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7,
                       t.channels,
                       t.comm,
                       "" variables
                  from core_ow_devs d left join core_ow_types t on d.rom_1 = t.code, 
                       core_controllers c
                 where d.controller_id = c.id
                   and d.id = '.$id.'
                order by c.name, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7';
        $data = DB::select($sql);
        if (count($data)) {
            $item = $data[0];
        } else {
            abort(404);
        }
        
        $item->rom = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
            $item->rom_1, 
            $item->rom_2, 
            $item->rom_3, 
            $item->rom_4, 
            $item->rom_5, 
            $item->rom_6, 
            $item->rom_7
        );
        
        $sql = 'select v.id, v.name, v.channel
                  from core_variables v 
                 where v.typ = "ow" 
                   and v.ow_id = '.$item->id.'
                order by v.channel';
                
        $item->devices = DB::select($sql);
        
        return view('admin.hubs.hosts.host-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * Route to delete host by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        try {
            \App\Http\Models\VariablesModel::whereTyp('ow')
                    ->whereOwId($id)
                    ->delete();
            $item = \App\Http\Models\OwDevsModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
