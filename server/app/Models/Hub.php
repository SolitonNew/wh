<?php

namespace App\Models;

use \App\Library\AffectsFirmwareModel;
use \Illuminate\Http\Request;
use Log;

class Hub extends AffectsFirmwareModel
{
    protected $table = 'core_hubs';
    public $timestamps = false;

    protected $_affectFirmwareFields = [
        'rom',
    ];
    
    public function extapiHosts()
    {
        return $this->hasMany(ExtApiHost::class);
    }
    
    public function owHosts()
    {
        return $this->hasMany(OwHost::class);
    }
    
    public function i2cHosts()
    {
        return $this->hasMany(I2cHost::class);
    }
    
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
    
    /**
     * 
     * @return int
     */
    public function hostsCount()
    {
        switch ($this->typ) {
            case 'extapi':
                return $this->extapiHosts->count();
            case 'din':
                return $this->owHosts->count();
            case 'orangepi':
                return $this->i2cHosts->count();
        }
        return 0;
    }
    
    /**
     * 
     * @param int $id
     * @return \App\Models\Hub
     */
    static public function findOrCreate(int $id)
    {
        $item = Hub::find($id);
        if (!$item) {
            $item = new Hub();
            $item->id = -1;
        }
        
        return $item;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     */
    static public function storeFromRequest(Request $request, int $id)
    {
        // Validation  ----------------------
        $rules = [];
        if ($request->typ == 'din') {
            $rules = [
                'name' => 'string|required',
                'typ' => 'string|required',
                'rom' => 'numeric|required|min:1|max:15|unique:core_hubs,rom,'.($id > 0 ? $id : ''),
                'comm' => 'string|nullable',
            ];
        } else {
            $rules = [
                'name' => 'string|required',
                'typ' => 'string|required',
                'comm' => 'string|nullable',
            ];
        }
        
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // Saving -----------------------
        try {
            $item = Hub::find($id);
            
            if (!$item) {
                $item = new Hub();
            }
            $item->name = $request->name;
            $item->typ = $request->typ;
            if ($item->typ == 'din') {
                $item->rom = $request->rom;
            } else {
                $item->rom = null;
            }
            $item->comm = $request->comm;
            $item->save();
            
            // Store event
            EventMem::addEvent(EventMem::HUB_LIST_CHANGE, [
                'id' => $item->id,
                'typ' => $item->typ,
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
            $item = Hub::find($id);
            if (!$item) abort(404);
            
            // Clear relations
            ExtApiHost::deleteByHubId($item->id);
            OwHost::deleteByHubId($item->id);
            I2cHost::deleteByHubId($item->id);
            
            foreach (Device::whereHubId($item->id)->get() as $device) {
                Device::deleteById($device->id);
            }
            // -------------------------------------

            $item->delete();
            // Store event
            EventMem::addEvent(EventMem::HUB_LIST_CHANGE, [
                'id' => $item->id,
                'typ' => $item->typ,
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
     * @var type 
     */
    static public $typs = [
        'extapi' => [
            'variable',
            'extapi',
        ],
        'orangepi' => [
            'variable',
            'orangepi',
            'i2c',
        ],
        'din' => [
            'variable',
            'din',
            'ow',
        ],
        'zigbeeone' => [
            'variable',
        ],
    ];
    
    /**
     *
     * @var type 
     */
    static private $_withNetworks = null;
    
    /**
     * 
     * @param int $hubID
     * @return type
     */
    static public function withNetworks(int $hubID)
    {
        if (self::$_withNetworks === null) {
            self::$_withNetworks = false;
            
            $hub = Hub::find($hubID);
            if ($hub) {
                $hubsWithNetworks = [
                    'din',
                    'orangepi',
                ];
                
                self::$_withNetworks = in_array($hub->typ, $hubsWithNetworks);
            }            
        }
        return self::$_withNetworks;
    }
    
    /**
     *
     * @var boolean
     */
    static private $_existsFirmwareHubs = null;
    
    /**
     * Returns true if there are hubs with firmware.
     * 
     * @return boolean
     */
    static public function existsFirmwareHubs()
    {
        if (self::$_existsFirmwareHubs === null) {
            self::$_existsFirmwareHubs = (Hub::whereTyp('din')->count() > 0);
        }
        
        return self::$_existsFirmwareHubs;
    }
    
    /**
     * 
     * @param type $typ
     * @return boolean
     */
    static public function isFirstSingleHub($typ)
    {
        $single = ['orangepi', 'zigbeeone'];
        if (in_array($typ, $single)) {
            return (self::whereTyp($typ)->count() === 0);
        }
        return true;
    }
}
