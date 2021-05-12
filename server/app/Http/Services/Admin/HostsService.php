<?php

namespace App\Http\Services\Admin;

use App\Models\Device;
use App\Models\OwDev;

class HostsService 
{
    /**
     * 
     * @return type
     */
    public function getIndexList(int $hubID)
    {
        $data = OwDev::whereHubId($hubID)
                    ->orderBy('rom_1', 'asc')
                    ->orderBy('rom_2', 'asc')
                    ->orderBy('rom_3', 'asc')
                    ->orderBy('rom_4', 'asc')
                    ->orderBy('rom_5', 'asc')
                    ->orderBy('rom_6', 'asc')
                    ->orderBy('rom_7', 'asc')
                    ->get();
        
        return $data;
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function getOneHost(int $id)
    {
        $item = OwDev::findOrFail($id);
        
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
            $item = OwDev::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
