<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;
use Lang;

class VariableChangesModel extends Model
{
    protected $table = 'core_variable_changes';
    public $timestamps = false;
    protected $primaryKey = 'ID';


}
