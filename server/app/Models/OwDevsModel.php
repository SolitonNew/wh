<?php

namespace App\Models;

use \App\Library\AffectsFirmwareModel;

class OwDevsModel extends AffectsFirmwareModel
{    
    protected $table = 'core_ow_devs';
    public $timestamps = false;
    
    protected $_affectFirmwareFields = [
        'id',
    ];
}
