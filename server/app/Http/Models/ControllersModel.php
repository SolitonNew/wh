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
}
