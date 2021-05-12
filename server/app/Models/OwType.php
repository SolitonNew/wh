<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwType extends Model
{
    protected $table = 'core_ow_types';
    public $timestamps = false;
    public $primaryKey = 'code';
    
    protected $fillable = [
        'code',
        'comm',
        'channels',
        'consuming',
    ];
}
