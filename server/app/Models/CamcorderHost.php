<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CamcorderHost extends AffectsFirmwareModel
{
    protected $table = 'core_camcorder_hosts';
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
                    ->whereTyp('camcorder')
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
            $types = config('camcorder.types');
            $type = [];
            if (isset($types[$this->typ])) {
                $type = $types[$this->typ];
                $type['title'] = Lang::get('admin/camcorders/'.$this->typ.'.title');
                $type['description'] = Lang::get('admin/camcorders/'.$this->typ.'.description');
                $type['properties'] = $this->_makePropertiesWithTitle($this->typ, $type['properties']);
            } else {
                $type['title'] = '';
                $type['description'] = '';
                $type['properties'] = [];
                $type['channels'] = [];
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
    
    private function _makePropertiesWithTitle($typ, $properies)
    {
        $result = [];
        foreach ($properies as $key => $size) {
            $result[$key] = (object)[
                'title' => Lang::get('admin/camcorders/'.$typ.'.'.$key),
                'size' => $size,
            ];
        }
        return $result;
    }
    
    /**
     * 
     * @return type
     */
    public function typeList()
    {
        $result = [];
        foreach (config('camcorder.types') as $type => $details) {
            $result[] = (object)[
                'name' => $type,
                'title' => Lang::get('admin/camcorders/'.$type.'.title'),
                'description' => Lang::get('admin/camcorders/'.$type.'.description'),
                'channels' => implode(';', $details['channels']),
                'properties' => $this->_makePropertiesWithTitle($type, $details['properties']),
            ];
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
        return self::whereHubId($hubID)
            ->orderBy('name', 'asc')
            ->get();
    }
    
    /**
     * 
     * @param int $hubID
     * @param int $id
     * @return \App\Models\CamcorderHost
     */
    static public function findOrCreate(int $hubID, int $id)
    {
        $item = self::whereHubId($hubID)
            ->whereId($id)
            ->first();
        
        if (!$item) {
            $item = new CamcorderHost();
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
            'name' => 'required',
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // Saving -----------------------
        
        try {
            $item = self::find($id);
            if (!$item) {
                $item = new CamcorderHost();
                $item->hub_id = $hubID;
                $item->typ = $request->typ;
            }
            
            $item->name = $request->name;
            
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
            
            // Store event
            EventMem::addEvent(EventMem::HOST_LIST_CHANGE, [
                'id' => $item->id,
                'hubID' => $item->hub_id,
            ]);
            // ------------
            
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
            // Clear relations
            foreach (Device::whereTyp('camcorder')->whereHostId($id)->get() as $device) {
                Device::deleteById($device->id);
            }
            // ------------------------
            
            $item = self::find($id);
            $item->delete();
            
            // Store event
            EventMem::addEvent(EventMem::HOST_LIST_CHANGE, [
                'id' => $item->id,
                'hubID' => $item->hub_id,
            ]);
            // ------------
            
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }
    
    /**
     * 
     * @param int $hubID
     */
    static public function deleteByHubId(int $hubID)
    {
        $result = 'OK';
        foreach (self::whereHubId($hubID)->get() as $host) {
            if (self::deleteById($host->id) != 'OK') {
                $result = 'With Errors';
            }
        }
    }
}
