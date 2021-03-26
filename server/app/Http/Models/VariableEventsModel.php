<?php

namespace App\Http\Models;

use \App\Library\AffectsFirmwareModel;

class VariableEventsModel extends AffectsFirmwareModel
{   
    protected $table = 'core_variable_events';
    public $timestamps = false;

    protected $_affectFirmwareFields = [];
}
