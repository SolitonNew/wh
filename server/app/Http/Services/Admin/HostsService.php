<?php

namespace App\Http\Services\Admin;

use App\Models\Device;
use App\Models\OwDevsModel;
use DB;

class HostsService 
{
    /**
     * 
     * @return type
     */
    public function getIndexList(int $hubID)
    {
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
        
        return $data;
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function getOneHost(int $id)
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
        
        return $item;
    }
    
    /**
     * 
     * @param int $id
     */
    public function delOneHost(int $id)
    {
        try {
            Device::whereTyp('ow')
                    ->whereOwId($id)
                    ->delete();
            $item = OwDevsModel::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
