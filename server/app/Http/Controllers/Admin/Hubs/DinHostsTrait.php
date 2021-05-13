<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Requests\Admin\OwHostRequest;
use App\Models\OwHost;

trait DinHostsTrait 
{
    public function dinIndex(int $hubID = null)
    {
        $data = OwHost::whereHubId($hubID)
                    ->orderBy('rom_1', 'asc')
                    ->orderBy('rom_2', 'asc')
                    ->orderBy('rom_3', 'asc')
                    ->orderBy('rom_4', 'asc')
                    ->orderBy('rom_5', 'asc')
                    ->orderBy('rom_6', 'asc')
                    ->orderBy('rom_7', 'asc')
                    ->get();
        
        return view('admin.hubs.hosts.din.din-hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => $data,
        ]);
    }
    
    public function dinEditShow(int $hubID, int $id)
    {
        $item = OwHost::findOrCreate($hubID, $id);
        
        return view('admin.hubs.hosts.din.din-host-edit', [
            'item' => $item,
        ]);
    }
    
    public function dinEditPost(OwHostRequest $request, int $hubID, int $id)
    {
        OwHost::storeFromRequest($request, $hubID, $id);
        
        return 'OK';
    }
    
    public function dinDelete(int $id)
    {
        OwHost::deleteById($id);
        
        return 'OK';
    }
}
