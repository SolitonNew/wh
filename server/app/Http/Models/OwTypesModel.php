<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class OwTypesModel extends Model
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
