<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPartsModel extends Model
{
    protected $table = 'plan_parts';
    public $timestamps = false;
    
    /**
     *  Формирует набор данных для дерева помещений
     * 
     * @return array
     */
    static public function generateTree() {
        $list = self::orderBy('ORDER_NUM', 'asc')
                    ->orderBy('NAME', 'asc')
                    ->get();
        
        $data = [];
        
        function genLevel($p_id, $level, &$list, &$data) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]->PARENT_ID == $p_id) {
                    $row = $list[$i];
                    $row->level = $level; 
                    $data[] = $row;
                    genLevel($row->ID, $level + 1, $list, $data);
                }
            }
        }
        
        genLevel(null, 0, $list, $data);
        
        return $data;
    }
    
    static public function genIDsForGroupAtParent($id) {
        $list = self::orderBy('ORDER_NUM', 'asc')
                    ->orderBy('NAME', 'asc')
                    ->get();
        
        $data = [$id];
        function genLevelIDs($p_id, &$list, &$data) {
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]->PARENT_ID == $p_id) {
                    $row = $list[$i];
                    $data[] = $row->ID;
                    genLevelIDs($row->ID, $list, $data);
                }
            }
        }
        
        genLevelIDs($id, $list, $data);
        
        return implode(', ', $data);
    }
}
