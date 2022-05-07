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
                    ->whereTyp('software')
                    ->orderBy('name', 'asc');
    }
    
    private $_driver = false;
    
    /**
     * 
     * @return type
     */
    public function driver()
    {
        if ($this->_driver === false) {
            foreach (config('softhosts.drivers') as $class) {
                $instance = new $class();
                if ($instance->name == $this->typ) {
                    $instance->assignKey($this->id);
                    $instance->assignData($this->data);
                    $this->_driver = $instance;
                    break;
                }
            }
        }
        
        return $this->_driver;        
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
            if ($this->driver()) {
                $type = [
                    'title' => $this->driver()->title,
                    'description' => $this->driver()->description,
                    'channels' => $this->driver()->channels,
                    'consuming' => 0,
                    'properties' => $this->driver()->propertiesWithTitles(),
                    'updated_at' => $this->driver()->getLastStorageDatetime(),
                ];
            } else {
                $type = [
                    'title' => '',
                    'description' => '',
                    'channels' => [],
                    'consuming' => 0,
                    'properties' => [],
                    'updated_at' => false,
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
        $result = [];
        foreach (config('softhosts.drivers') as $class) {
            $result[] = new $class();
        }
        return $result;
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
        // Validation  ----------------------
        $rules = [
            'typ' => ($id == -1) ? 'required' : 'nullable',
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // Saving -----------------------
        
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
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
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
            
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }
}
