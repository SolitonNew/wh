<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;

class VariableChangesModel extends Model
{
    protected $table = 'core_variable_changes_mem';
    
    static private $_lastVariableID = -1;
    
    static public function lastVariableID() {
        if (self::$_lastVariableID == -1) {
            $res = DB::select('select max(ID) MAX_ID from core_variable_changes_mem');
            if (count($res) && ($res[0]->MAX_ID > 0)) {
                self::$_lastVariableID = $res[0]->MAX_ID;
            }
        }
        return self::$_lastVariableID;
    }
}
