<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use App\Library\AffectsFirmwareModel;

/**
 * Description of SoftHost
 *
 * @author soliton
 */
class SoftHost extends AffectsFirmwareModel
{
    protected $table = 'core_soft_hosts';
    public $timestamps = false;
    
    
}
