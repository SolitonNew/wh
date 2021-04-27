<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Log;

class PlanPartsModel extends Model
{
    protected $table = 'plan_parts';
    public $timestamps = false;
    
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
    static public function getAllPartsCache() 
    {
        if (self::$_all_parts_cache == null) {
            self::$_all_parts_cache = self::orderBy('order_num', 'asc')
                                            ->get();
        }
        
        return self::$_all_parts_cache;
    }
        
    /**
     * Формирует набор данных для дерева помещений.
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
        
        // Формируем древовидность с помощью символов псевдографики
        $levels = [];
        $prev_level = -1;
        for ($i = count($data) - 1; $i > -1; $i--) {
            // Чистим существующие записи уровней до текущего вложения
            for ($n = $prev_level + 1; $n <= $data[$i]->level; $n++) {
                $levels[$n] = false;
            }
            
            // Заносим состояния уровней в путь вывода
            $path = [];
            for ($n = 0; $n < $data[$i]->level; $n++) {
                if (isset($levels[$n]) && $levels[$n]) {
                    $path[] = $asChars ? '│&nbsp;&nbsp;' : 1;
                } else {
                    $path[] = $asChars ? '&nbsp;&nbsp;&nbsp;' : 2;
                }
            }
            
            // Проверяем является ли запис последним узлом
            $n = $data[$i]->level;
            if (isset($levels[$n]) && $levels[$n]) {
                $path[count($path)] = $asChars ? '├─' : 3;
            } else {
                $path[count($path)] = $asChars ? '└─' : 4;
            }
            
            // Отмечаем, что этот уровень мы используем
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
     * Выполняет перемещение вложенных записей плана с перещетом их координат.
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
     * Выполняет проверку записи плана является ли она вложенной записью.
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
     * Выполняет расчет количества вложений в структуре плана и записывает 
     * результат в propertys.
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
     * Возвращает координаты $parentId
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
     * Возвращает строку пути к $id, где отдельные узлы разделены $delimeter.
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
}
