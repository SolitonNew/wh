<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;

class SoftHost extends AffectsFirmwareModel
{
    protected $table = 'core_soft_hosts';
    public $timestamps = false;
    
    /**
     * 
     * @param int $hubID
     * @param int $id
     * @return \App\Models\SoftHost
     */
    static public function findOrCreate(int $hubID, int $id)
    {
        $item = SoftHost::whereHubId($hubID)->whereId($id)->first();
        if (!$item) {
            $item = new SoftHost();
            $item->id = $id;
            $item->hubId = $hubID;
        }
        
        return $item;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $hubID
     * @param int $id
     */
    static public function storeFromRequest(Request $request, int $hubID, int $id)
    {
        
    }
    
    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {
            Device::whereTyp('software')
                    ->whereSoftId($id)
                    ->delete();
            $item = SoftHost::find($id);
            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
