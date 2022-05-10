<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class ExtApiHostStorage extends Model
{
    protected $table = 'core_extapi_host_storages';
    public $timestamps = false;
    
    protected $fillable = [
        'extapi_host_id',
        'data',
    ];
}
