<?php

namespace App\Models;

use \App\Library\AffectsFirmwareModel;
use \Illuminate\Http\Request;

class Hub extends AffectsFirmwareModel
{
    protected $table = 'core_hubs';
    public $timestamps = false;

    protected $_affectFirmwareFields = [
        'rom',
    ];
    
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
            $item = Hub::find($id);
            if (!$item) abort(404);

            $item->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     *
     * @var type 
     */
    static public $typs = [
        'software' => [
            'variable',
            'software',
        ],
        'din' => [
            'variable',
            'din',
            'ow',
        ],
        /*'onewire' => [
            'variable',
            'ow',
        ],
        'zigbee' => [
            'variable',
        ],*/
    ];
    
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
}
