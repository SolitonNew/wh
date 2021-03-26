<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;

class ScriptsModel extends AffectsFirmwareModel
{    
    protected $table = 'core_scripts';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'data',
    ];
}
