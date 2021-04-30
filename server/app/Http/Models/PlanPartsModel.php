<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

class PlanPartsModel extends Model
{
    protected $table = 'plan_parts';
    public $timestamps = false;
    
    /**
     * The chache of the all plan records used to build the tree.
     * Used in many places in one session.
     * 
     * @var type 
     */
    static private $_all_parts_cache = null;
    
    /**
     * Returns a cache of all plan entries.
     * If the cache is not initialized, loading from the database is performed.
     * 
     * @return type
     */
    static public function getAllPartsCache() 
    {
        if (self::$_all_parts_cache == null) {
            self::$_all_parts_cache = self::orderBy('order_num', 'asc')
                                            ->get();
        }
        
        return self::$_all_parts_cache;
    }
        
    /**
     * The tree data generator.
     * 
     * @return array
     */
    static public function generateTree(int $id = null, bool $asChars = true) 
    {       
        $data = [];
        
        $treeLevel = function ($p_id, $level) use (&$treeLevel, &$data) {
            foreach(self::getAllPartsCache() as $row) {
                if ($row->parent_id == $p_id) {
                    $item = $row;
                    $item->level = $level;
                    $data[] = $item;
                    $treeLevel($row->id, $level + 1);
                }
            }
        };
        
        foreach(self::getAllPartsCache() as $row) {
            if ($row->id == $id) {
                $data[] = $row;
                $data[0]->level = 0;
                break;
            }
        }
        
        $treeLevel($id, 0);
        
        // Forming a tree using pseudo-graphic symbols
        $levels = [];
        $prev_level = -1;
        for ($i = count($data) - 1; $i > -1; $i--) {
            // Чистим существующие записи уровней до текущего вложения
            for ($n = $prev_level + 1; $n <= $data[$i]->level; $n++) {
                $levels[$n] = false;
            }
            
            // Putting the states of the levels in the output path
            $path = [];
            for ($n = 0; $n < $data[$i]->level; $n++) {
                if (isset($levels[$n]) && $levels[$n]) {
                    $path[] = $asChars ? '│&nbsp;&nbsp;' : 1;
                } else {
                    $path[] = $asChars ? '&nbsp;&nbsp;&nbsp;' : 2;
                }
            }
            
            // Checking if the entry is the last node
            $n = $data[$i]->level;
            if (isset($levels[$n]) && $levels[$n]) {
                $path[count($path)] = $asChars ? '├─' : 3;
            } else {
                $path[count($path)] = $asChars ? '└─' : 4;
            }
            
            // Note that we are using this level
            $levels[$n] = true;
            
            $prev_level = $n;
            
            if (count($path)) {
                $path = array_slice($path, 1);
            }
            
            if ($asChars) {
                $data[$i]->treePath = implode('', $path);
            } else {
                $data[$i]->treePath = $path;
            }
        }
        
        // --------------------------------------------------------
        
        return $data;
    }
       
    /**
     * Returns ids of all child plans starting with parent_id
     * 
     * @param type $id
     * @return type
     */
    static public function genIDsForGroupAtParent($id) 
    {       
        $data = [$id];
        
        $genLevel = function ($p_id) use (&$genLevel, &$data) {
            foreach(self::getAllPartsCache() as $row) {
                if ($row->parent_id == $p_id) {
                    $data[] = $row->id;
                    $genLevel($row->id);
                }
            }
        };
        
        $genLevel($id);
        
        return implode(', ', $data);
    }
    
    /**
     * Performs movement of nested plan records with recalculation of their 
     * coordinates.
     * 
     * @param float $dx
     * @param float $dy
     */
    public function moveChilds(float $dx, float $dy) 
    {
        $ids = explode(',', self::genIDsForGroupAtParent($this->id));
        
        foreach(PlanPartsModel::whereIn('id', $ids)->cursor() as $row) {
            if ($row->id == $this->id) continue;
            
            $bounds = json_decode($row->bounds);
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
            $row->bounds = json_encode($bounds);
            $row->save();
        }
    }
    
    /**
     * Checks if a plan entry is a nested entry.
     * 
     * @param int $id
     * @param int $parentID
     * @return boolean
     */
    static public function checkIdAsChildOfParentID(int $id, int $parentID) 
    {
        if ($id == $parentID) {
            return false;
        }
        
        $list = self::getAllPartsCache();
        
        $curr_id = $id;
        do {
            foreach($list as $row) {
                if ($row->id == $curr_id) {
                    $curr_id = $row->parent_id;
                    if ($curr_id == $parentID) {
                        return false;
                    }
                    break;
                }
            }
        } while ($curr_id != null);
        
        return true;
    }
    
    /**
     * 
     */
    static public function calcAndStoreMaxLevel() 
    {
        // Пройдемся по структуре и посчитаем уровни
        
        $maxLevel = 0;
        self::$_all_parts_cache = null;
        foreach(self::generateTree() as $row) {
            if ($row->level > $maxLevel) {
                $maxLevel = $row->level;
            }
        }
        
        if ($maxLevel > 2) $maxLevel = 2;
        
        $maxLevel++;
        
        if (PropertysModel::getPlanMaxLevel() > $maxLevel) {
            PropertysModel::setPlanMaxLevel($maxLevel);
        }
    }
    
    /**
     * Returns $parentId coords
     *
     * @param type $parentId
     * @return type
     */
    static public function parentOffset($parentId) 
    {
        $parent = PlanPartsModel::find($parentId);
        if ($parent) {
            $bounds = json_decode($parent->bounds);
            return (object)[
                'X' => $bounds->X,
                'Y' => $bounds->Y,
            ];
        }
        
        return (object)[
            'X' => 0,
            'Y' => 0,
        ];
    }
    
    /**
     * Returns the path string to $id, where the individual nodes are 
     * separated by $delimiter.
     * 
     * @param type $id
     * @param type $delimeter
     * @return type
     */
    static public function getPath($id, $delimeter) 
    {
        $path = [];
        
        $genLevel = function ($id) use (&$genLevel, &$path) {
            foreach (self::getAllPartsCache() as $row) {
                if ($row->id === $id) {
                    $path[] = $row->name;
                    $genLevel($row->parent_id);
                    break;
                }
            }
        };
        
        $genLevel($id);
        
        return implode($delimeter, array_reverse($path));
    }
    
    /**
     * Performs import of the plan records from a string.
     * 
     * @param string $data
     * @return string
     */
    static public function importFromString(string $data) 
    {
        $storeLevel = function ($level, $parentID) use (&$storeLevel) {
            $i = 1;
            foreach($level as $item) {
                $plan = new \App\Http\Models\PlanPartsModel();
                $plan->id = $item->id;
                $plan->parent_id = $parentID;
                $plan->name = $item->name;
                $plan->bounds = $item->bounds;
                $plan->style = $item->style;
                $plan->ports = $item->ports;
                $plan->order_num = $i++;
                $plan->save();                    
                $storeLevel($item->childs, $item->id);
            }
        };
            
        try {
            // Декодируем
            $parts = json_decode($data);

            // Удаляем все существующиезаписи из БД
            \App\Http\Models\PlanPartsModel::truncate();

            // Рекурсивно заливаем новые записи
            $storeLevel($parts, null);

            // Нужно пересчитать максимальный уровень вложения структуры
            \App\Http\Models\PlanPartsModel::calcAndStoreMaxLevel();

            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'error' => [$ex->getMessage()],
            ]);
        }
    }
    
    /**
     * Performs export of the plan records to a string
     * 
     * @return string
     */
    static public function exportToString(): string
    {
        $parts = self::orderBy('order_num', 'asc')->get();
        
        $loadLevel = function ($parentID) use (&$loadLevel, $parts) {
            $res = [];
            foreach($parts as $part) {
                if ($part->parent_id == $parentID) {
                    $res[] = (object)[
                        'id' => $part->id,
                        'name' => $part->name,
                        'bounds' => $part->bounds,
                        'style' => $part->style,
                        'ports' => $part->ports,
                        'childs' => $loadLevel($part->id),
                    ];
                }
            }
            return $res;
        };
        
        return json_encode($loadLevel(null));
    }
}
