<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;
use \Illuminate\Http\Request;

class ControllersModel extends AffectsFirmwareModel
{
    protected $table = 'core_controllers';
    public $timestamps = false;

    protected $_affectFirmwareFields = [
        'rom',
    ];
    
    /**
     * 
     * @param int $id
     * @return \App\Http\Models\ControllersModel
     */
    static public function findOrCreate(int $id)
    {
        $item = ControllersModel::find($id);
        if (!$item) {
            $item = new ControllersModel();
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
            $item = ControllersModel::find($id);
            
            if (!$item) {
                $item = new ControllersModel();
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
            $item = ControllersModel::find($id);
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
            self::$_existsFirmwareHubs = (ControllersModel::whereTyp('din')->count() > 0);
        }
        
        return self::$_existsFirmwareHubs;
    }
}
