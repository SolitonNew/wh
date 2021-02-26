<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ScriptsModel extends Model
{
    protected $table = 'core_scripts';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    
}
