<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;
use Log;

class SoftHost extends AffectsFirmwareModel
{
    protected $table = 'core_soft_hosts';
    public $timestamps = false;
    
    /**
     * 
     * @return type
     */
    public function hub()
    {
        return $this->belongsTo(Hub::class, 'hub_id');
    }
    
    /**
     * 
     * @return type
     */
    public function devices()
    {
        return $this->hasMany(Device::class, 'ow_id')
                    ->whereTyp('ow')
                    ->orderBy('name', 'asc');
    }
    
    /**
     * 
     * @return string
     */
    public function typeName()
    {
        return '';
    }
    
    /**
     * 
     * @param int $hubID
     * @return type
     */
    static public function listForIndex(int $hubID)
    {
        return SoftHost::whereHubId($hubID)
            ->orderBy('name', 'asc')
            ->get();
    }
    
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
            $item->hub_id = $hubID;
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
        try {
            $item = self::find($id);
            if (!$item) {
                $item = new SoftHost();
                $item->hub_id = $hubID;
                $item->name = 'Software Host';
            }

            $item->typ = $request->typ;
            $item->save();
            
            return 'OK';
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
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
