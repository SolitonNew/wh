<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;

class ControllersModel extends AffectsFirmwareModel
{
    protected $table = 'core_controllers';
    public $timestamps = false;

    protected $_affectFirmwareFields = [
        'rom',
    ];
    
    /**
     *
     * @var type 
     */
    static public $typs = [
        'software' => [
            'variable',
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
