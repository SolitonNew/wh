<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Requests\Admin\SoftHostRequest;
use App\Models\SoftHost;

trait SoftHostsTrait 
{    
    public function softIndex(int $hubID = null)
    {
        $data = SoftHost::whereHubId($hubID)
                    ->orderBy('name', 'asc')
                    ->get();
        
        return view('admin.hubs.hosts.soft.soft-hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => $data,
        ]);
    }
    
    public function softEditShow(int $hubID, int $id)
    {
        $item = SoftHost::findOrCreate($hubID, $id);
        
        return view('admin.hubs.hosts.soft.soft-host-edit', [
            'item' => $item,
        ]);        
    }
    
    public function softEditPost(SoftHostRequest $request, int $hubID, int $id)
    {
        SoftHost::storeFromRequest($request, $hubID, $id);
        
        return 'OK';
    }
    
    public function softDelete(int $id)
    {
        SoftHost::deleteById($id);
        
        return 'OK';
    }
}
