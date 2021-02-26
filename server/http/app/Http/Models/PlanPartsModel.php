<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

class PlanPartsModel extends Model
{
    protected $table = 'plan_parts';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    
    /**
     * 
     */
    static public function boot() {
        parent::boot();
        
        self::updating(function($model) {
            self::_updatingHandler($model);
        });
    }
    
    /**
     * Кеш всех записей, которые могут использоваться для построения деревьев
     * Используется во многих местах в одном сеансе.
     * 
     * @var type 
     */
    static private $_all_parts_cache = null;
    
    /**
     * Возвращает кеш полного списка. Если список изначально не инициализирован - делает запрос к БД
     * @return type
     */
    static public function getAllPartsCache() {
        if (self::$_all_parts_cache == null) {
            self::$_all_parts_cache = self::orderBy('ORDER_NUM', 'asc')
                                            //->orderBy('NAME', 'asc')
                                            ->get();
        }
        
        return self::$_all_parts_cache;
    }
    
    /**
     * 
     * @param type $p_id
     * @param type $level
     * @param type $list
     * @param type $data
     */
    static private function _generateTreeLevel($p_id, $level, &$data) {
        foreach(self::getAllPartsCache() as $row) {
            if ($row->PARENT_ID == $p_id) {
                $item = $row;
                $item->level = $level;
                $data[] = $item;
                self::_generateTreeLevel($row->ID, $level + 1, $data);
            }
        }
    }
    
    /**
     *  Формирует набор данных для дерева помещений
     * 
     * @return array
     */
    static public function generateTree(int $id = null) {       
        $data = [];
        
        foreach(self::getAllPartsCache() as $row) {
            if ($row->ID == $id) {
                $data[] = $row;
                $data[0]->level = 0;
                break;
            }
        }
        
        self::_generateTreeLevel($id, 0, $data);
        
        return $data;
    }
    
    /**
     * 
     * @param type $p_id
     * @param type $list
     * @param type $data
     */
    static public function _genLevelIDsForGroupAtParent($p_id, &$data) {
        foreach(self::getAllPartsCache() as $row) {
            if ($row->PARENT_ID == $p_id) {
                $data[] = $row->ID;
                self::_genLevelIDsForGroupAtParent($row->ID, $data);
            }
        }
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    static public function genIDsForGroupAtParent($id) {       
        $data = [$id];
        self::_genLevelIDsForGroupAtParent($id, $data);
        
        return implode(', ', $data);
    }
    
    /**
     * 
     * @param type $model
     */
    static private function _updatingHandler($model) {
        //
    }
    
    /**
     * 
     * @param float $dx
     * @param float $dy
     */
    public function moveChilds(float $dx, float $dy) {
        $ids = explode(',', self::genIDsForGroupAtParent($this->ID));
        
        foreach(PlanPartsModel::whereIn('ID', $ids)->cursor() as $row) {
            if ($row->ID == $this->ID) continue;
            
            $bounds = json_decode($row->BOUNDS);
            if (!$bounds) {
                $bounds = (object)[
                    'X' => 0,
                    'Y' => 0,
                    'W' => 10,
                    'H' => 6,
                ];
            }
            $bounds->X += $dx;
            $bounds->Y += $dy;
            $row->BOUNDS = json_encode($bounds);
            $row->save();
        }
    }
    
    static public function checkIdAsChildOfParentID(int $id, int $parentID) {
        if ($id == $parentID) {
            return false;
        }
        
        $list = self::getAllPartsCache();
        
        $curr_id = $id;
        do {
            foreach($list as $row) {
                if ($row->ID == $curr_id) {
                    $curr_id = $row->PARENT_ID;
                    if ($curr_id == $parentID) {
                        return false;
                    }
                    break;
                }
            }
        } while($curr_id != null);
        
        return true;
    }
    
}
