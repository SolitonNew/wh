<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class I2cHost extends AffectsFirmwareModel
{
    protected $table = 'core_i2c_hosts';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'id',
    ];
    
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
                    ->whereTyp('i2c')
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
            $types = config('i2c.types');
            $type = isset($types[$this->typ]) ? $types[$this->typ] : [];

            if (!isset($type['description'])) {
                $type['description'] = '';
            }
            
            if (!isset($type['address'])) {
                $type['address'] = [];
            }

            if (!isset($type['channels'])) {
                $type['channels'] = [];
            }

            if (!isset($type['consuming'])) {
                $type['consuming'] = 0;
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
     * @return \App\Models\class
     */
    public function typeList()
    {
        $result = [];
        foreach (config('i2c.types') as $type => $details) {
            $result[] = (object)[
                'name' => $type,
                'description' => $details['description'],
                'address' => implode(';', $details['address']),
                'channels' => implode(';', $details['channels']),
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
            ->orderBy('typ', 'asc')
            ->orderBy('address', 'asc')
            ->get();
    }
    
    /**
     * 
     * @param int $hubID
     * @param int $id
     * @return \App\Models\I2cHost
     */
    static public function findOrCreate(int $hubID, int $id)
    {
        $item = self::whereHubId($hubID)
            ->whereId($id)
            ->first();
        if (!$item) {
            $item = new I2cHost();
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
     * @return string
     */
    static public function storeFromRequest(Request $request, int $hubID, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'typ' => 'string|required',
            'address' => 'numeric|required|unique:core_i2c_hosts,address,'.($id > 0 ? $id : ''),
            'comm' => 'string|nullable',
        ];
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // Saving -----------------------
        try {
            $item = I2cHost::find($id);
            
            if (!$item) {
                $item = new I2cHost();
                $item->hub_id = $hubID;
            }
            $item->name = $request->typ;
            $item->comm = $request->comm;
            $item->typ = $request->typ;
            $item->address = $request->address;
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
     * @return string
     */
    static public function deleteById(int $id)
    {
        try {
            // Clear relations
            foreach (Device::whereTyp('i2c')->whereHostId($id)->get() as $device) {
                Device::deleteById($device->id);
            }
            // -------------------------
            
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
            Log::error($ex->getMessage());
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
        return $result;
    }
}
