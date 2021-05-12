<?php

namespace App\Models;

use \App\Library\AffectsFirmwareModel;

class OwDev extends AffectsFirmwareModel
{    
    protected $table = 'core_ow_devs';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'id',
    ];
}
