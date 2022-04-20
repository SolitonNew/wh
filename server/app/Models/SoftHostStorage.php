<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SoftHostStorage extends Model
{
    protected $table = 'core_soft_host_storages';
    public $timestamps = false;
    
    protected $fillable = [
        'soft_host_id',
        'data',
    ];
}
