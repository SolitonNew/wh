<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;

class VariableChangesModel extends Model
{
    protected $table = 'core_variable_changes_mem';
    public $timestamps = false;
    protected $primaryKey = 'ID';

    /**
     *
     * @var type
     */
    static private $_lastVariableID = -1;

    /**
     *
     * @return type
     */
    static public function lastVariableID() {
        if (self::$_lastVariableID == -1) {
            $res = DB::select('select max(ID) MAX_ID from core_variable_changes_mem');
            if (count($res) && ($res[0]->MAX_ID > 0)) {
                self::$_lastVariableID = $res[0]->MAX_ID;
            }
        }
        return self::$_lastVariableID;
    }

    /**
     *
     * @param type $id
     */
    static public function setLastVariableID($id) {
        self::$_lastVariableID = $id;
    }

    /**
     *
     * @param type $lastID
     * @param type $count
     * @return type
     */
    static public function getLastVariables() {
        if (self::$_lastVariableID > 0) {
            $sql = 'select m.ID, m.CHANGE_DATE, m.VALUE, v.COMM, v.APP_CONTROL, m.VARIABLE_ID
                      from core_variable_changes_mem m, core_variables v
                     where m.VARIABLE_ID = v.ID
                       and m.ID > '.self::$_lastVariableID.'
                    order by m.ID desc';
        } else {
            $sql = 'select m.ID, m.CHANGE_DATE, m.VALUE, v.COMM, v.APP_CONTROL, m.VARIABLE_ID
                      from core_variable_changes_mem m, core_variables v
                     where m.VARIABLE_ID = v.ID
                    order by m.ID desc
                    limit '.config('app.admin_log_lines_count');
        }
        return DB::select($sql);
    }

    /**
     *
     * @param type $app_control
     * @param type $value
     * @return type
     */
    static public function decodeLogValue($app_control, $value) {
        $dim = \Illuminate\Support\Facades\Lang::get('admin/variables.log_app_control_dim.'.$app_control);
        if (is_array($dim)) {
            return $dim[$value];
        } else {
            return $value.$dim;
        }
    }
}
