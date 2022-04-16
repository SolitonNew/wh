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
        return $this->hasMany(Device::class, 'host_id')
                    //->whereTyp('software')
                    ->orderBy('name', 'asc');
    }
    
    /**
     *
     * @var type 
     */
    public $type = null;
    
    /**
     * 
     * @return type
     */
    public function type()
    {
        if ($this->type === null) {
            $manager = new \App\Library\SoftHosts\SoftHostsManager();
            
            $provider = $manager->providerByName($this->typ);
            
            if ($provider) {
                $type = [
                    'title' => $provider->title,
                    'description' => $provider->description,
                    'channels' => $provider->channels,
                    'consuming' => 0,
                    'properties' => $provider->propertiesWithTitles(),
                ];
            } else {
                $type = [
                    'title' => '',
                    'description' => '',
                    'channels' => [],
                    'consuming' => 0,
                    'properties' => [],
                ];
            }
            
            $this->type = (object)$type;
        }
        
        return $this->type;
    }
    
    /**
     * 
     * @return type
     */
    public function channelsOfType()
    {
        if ($this->type()) {
            return $this->type()->channels;
        }
        
        return [];
    }
    
    /**
     * 
     * @return type
     */
    public function typeList()
    {
        $manager = new \App\Library\SoftHosts\SoftHostsManager();
        return $manager->providers();
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
                $item->typ = $request->typ;
            }
            
            // Store properties data
            $propertiesData = [];
            $properties = $item->type()->properties;
            $i = 0;
            foreach ($properties as $key => $val) {
                $propertiesData[$key] = $request->get($key);
            }
            $item->data = json_encode($propertiesData);
            // ---------------------
            
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
                    ->whereHostId($id)
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
