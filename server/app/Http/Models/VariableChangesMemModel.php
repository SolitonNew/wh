<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;
use Lang;

class VariableChangesMemModel extends Model
{
    protected $table = 'core_variable_changes_mem';
    public $timestamps = false;

    /**
     *
     * @var type
     */
    static private $_lastVariableID = -1;

    /**
     *
     * @return type
     */
    static public function lastVariableID() 
    {
        if (self::$_lastVariableID == -1) {
            $res = DB::select('select max(ID) max_id from core_variable_changes_mem');
            if (count($res) && ($res[0]->max_id > 0)) {
                self::$_lastVariableID = $res[0]->max_id;
            }
        }
        return self::$_lastVariableID;
    }

    /**
     *
     * @param type $id
     */
    static public function setLastVariableID($id) 
    {
        self::$_lastVariableID = $id;
    }

    /**
     *
     * @param type $lastID
     * @param type $count
     * @return type
     */
    static public function getLastVariables() 
    {
        if (self::$_lastVariableID > 0) {
            $sql = 'select m.id, m.change_date, m.value, v.comm, v.app_control, m.variable_id,
                           (select p.name from plan_parts p where p.id = v.group_id) group_name
                      from core_variable_changes_mem m, core_variables v
                     where m.variable_id = v.id
                       and m.id > '.self::$_lastVariableID.'
                    order by m.id desc';
        } else {
            $sql = 'select m.id, m.change_date, m.value, v.comm, v.app_control, m.variable_id,
                           (select p.name from plan_parts p where p.id = v.group_id) group_name
                      from core_variable_changes_mem m, core_variables v
                     where m.variable_id = v.id
                    order by m.id desc
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
    static public function decodeLogValue($app_control, $value) 
    {
        $dim = Lang::get('admin/hubs.log_app_control_dim.'.$app_control);
        if (is_array($dim)) {
            if (isset($dim[$value])) {
                return $dim[$value];
            } else {
                return $value;
            }
        } else {
            return $value.$dim;
        }
    }
}
