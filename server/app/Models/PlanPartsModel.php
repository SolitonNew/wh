<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\VariablesModel;
use DB;

class PlanPartsModel extends Model
{
    protected $table = 'plan_parts';
    public $timestamps = false;
    
    /**
     * Load plan records with port records and with devices.
     * 
     * @param int $id
     * @return type
     */
    static public function listAllForIndex(int $id)
    {
        $ports = [];
        $parts = PlanPartsModel::generateTree($id);
        foreach($parts as $row) {
            if ($row->bounds) {
                $v = json_decode($row->bounds);
            } else {
                $v = (object)[
                    'X' => 0,
                    'Y' => 0,
                    'W' => 10,
                    'H' => 6,
                ];
            }
            $row->X = $v->X;
            $row->Y = $v->Y;
            $row->W = $v->W;
            $row->H = $v->H;
            
            if ($row->style) {
                $v = json_decode($row->style);
            } else {
                $v = (object)[];
            }
            
            $row->pen_style = isset($v->pen_style) ? $v->pen_style : 'solid';
            $row->pen_width = isset($v->pen_width) ? $v->pen_width : 1;
            $row->fill = isset($v->fill) ? $v->fill : 'background';
            $row->name_dx = isset($v->name_dx) ? $v->name_dx : 0;
            $row->name_dy = isset($v->name_dy) ? $v->name_dy : 0;

            // Packed port data
            if ($row->ports) {
                foreach(json_decode($row->ports) as $index => $port) {
                    $ports[] = (object)[
                        'id' => count($ports),
                        'index' => $index,
                        'partID' => $row->id,
                        'position' => json_encode($port),
                        'partBounds' => $row->bounds,
                    ];
                }
            }
        }
        
        // Load list of the devices
        $devices = [];
        foreach(VariablesModel::get() as $device) {
            $part = false;
            foreach($parts as $row) {
                if ($device->group_id == $row->id) {
                    $part = $row;
                    break;
                }
            }
            
            if ($part) {
                $device->partBounds = $part->bounds;
                $devices[] = $device;
            }
        }
        
        return [$parts, $ports, $devices];
    }
    
    /**
     * 
     * @param int $id
     * @param int $p_id
     * @return string
     */
    static public function findOrCreate(int $id, int $p_id = -1)
    {
        $item = PlanPartsModel::find($id);
        if (!$item) {
            $item = new PlanPartsModel();
            $item->id = -1;
            $item->parent_id = $p_id;
        }
        
        return $item;
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    static public function storeFromRequest(Request $request, int $id)
    {
        try {
            $item = PlanPartsModel::find($id);
            
            $off = PlanPartsModel::parentOffset($request->parent_id);

            $dx = 0;
            $dy = 0;                
            if (!$item) {
                $item = new PlanPartsModel();
            } else {
                $bounds = json_decode($item->bounds);
                if ($bounds) {
                    $dx = $request->X + $off->X - $bounds->X;
                    $dy = $request->Y + $off->Y - $bounds->Y;
                }
            }

            $item->parent_id = $request->parent_id;
            $item->name = $request->name;

            $item->bounds = json_encode([
                'X' => $request->X + $off->X,
                'Y' => $request->Y + $off->Y,
                'W' => $request->W,
                'H' => $request->H,
            ]);
            $item->style = json_encode([
                'pen_style' => $request->pen_style,
                'pen_width' => $request->pen_width,
                'fill' => $request->fill,
                'name_dx' => $request->name_dx ?? 0,
                'name_dy' => $request->name_dy ?? 0,
            ]);
            $item->save();

            if (($dx != 0) || ($dy != 0)) {
                $item->moveChilds($dx, $dy);
            }

            if ($id == -1) {
                $item->order_num = $item->id;
                $item->save();
            }

            // Recalc max level
            PlanPartsModel::calcAndStoreMaxLevel();

            return 'OK';
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $id
     */
    static public function deleteById(int $id)
    {
        try {
            $item = PlanPartsModel::find($id);
            $item->delete();
            
            // Recalc max level
            PlanPartsModel::calcAndStoreMaxLevel();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }        
    }
    
    /**
     * 
     * @param int $id
     * @param string $direction
     */
    static public function cloneNearby(int $id, string $direction) 
    {
        try {
            $part = PlanPartsModel::find($id);
            
            if (!$part) abort(404);
            
            $new_part = new PlanPartsModel();

            $new_part->parent_id = $part->parent_id;
            $new_part->name = $part->name.' copy';
            $new_part->style = $part->style;

            $bounds = json_decode($part->bounds);
            switch ($direction) {
                case 'top':
                    $bounds->Y -= $bounds->H;
                    break;
                case 'right':
                    $bounds->X += $bounds->W;
                    break;
                case 'bottom':
                    $bounds->Y += $bounds->H;
                    break;
                case 'left':
                    $bounds->X -= $bounds->W;
                    break;
            }
            $new_part->bounds = json_encode($bounds);

            $new_part->save();
            $new_part->order_num = $new_part->id;
            $new_part->save();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }        
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     */
    static public function moveChildsFromRequest(Request $request, int $id)
    {
        try {
            $item = PlanPartsModel::find($id);
            $item->moveChilds($request->DX, $request->DY);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $parentId
     * @return type
     */
    static public function childList(int $parentId)
    {
        return PlanPartsModel::whereParentId($parentId)
                    ->orderBy('order_num', 'asc')
                    ->get();
    }
    
    /**
     * 
     * @param Request $request
     */
    static public function setChildListOrdersFromRequest(Request $request)
    {
        try {
            $ids = explode(',', $request->orderIds);

            foreach (PlanPartsModel::find($ids) as $item) {
                $item->order_num = array_search($item->id, $ids);
                $item->save();
            }            
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $id
     * @param float $newX
     * @param float $newY
     */
    static public function move(int $id, float $newX, float $newY)
    {
        try {
            $item = PlanPartsModel::find($id);
            if (!$item) abort(404);
            $bounds = json_decode($item->bounds);
            $bounds->X = $newX;
            $bounds->Y = $newY;
            $item->bounds = json_encode($bounds);
            $item->save();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $id
     * @param float $newW
     * @param float $newH
     */
    static public function size(int $id, float $newW, float $newH)
    {
        try {
            $item = PlanPartsModel::find($id);
            if (!$item) abort(404);
            
            $bounds = json_decode($item->bounds);
            $bounds->W = $newW;
            $bounds->H = $newH;
            $item->bounds = json_encode($bounds);
            $item->save();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param Request $request
     */
    static public function importFromRequest(Request $request)
    {
        try {
            $data = file_get_contents($request->file('file'));
            PlanPartsModel::importFromString($data);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param Request $request
     * @param int $planID
     * @param int $deviceID
     */
    static public function linkDeviceFromRequest(Request $request, int $planID, int $deviceID)
    {
        $deviceID = $request->device ?? $deviceID;

        $device = VariablesModel::find($deviceID);
        if (!$device) abort(404);
        
        try {
            $position = (object)[
                'surface' => $request->surface,
                'offset' => $request->offset,
                'cross' => $request->cross,
            ];
            $device->group_id = $planID;
            $device->position = json_encode($position);
            $device->save();            
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @return type
     */
    static public function devicesForLink()
    {
        $sql = "select v.*
                  from core_variables v
                 where not exists(select 1 from plan_parts p where p.id = v.group_id)
                order by v.name";
        $devices = DB::select($sql);
        
        foreach($devices as $dev) {
            $dev->label = $dev->name.' '.($dev->comm);
            $app_control = VariablesModel::decodeAppControl($dev->app_control);
            $dev->label .= ' '."'$app_control->label'";
        }
        
        return $devices;
    }
    
    /**
     * 
     * @param int $deviceID
     */
    static public function unlinkDevice(int $deviceID)
    {
        $device = VariablesModel::find($deviceID);
        if (!$device) abort(404);
        
        try {    
            $device->group_id = -1;
            $device->position = null;
            $device->save();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param Request $request
     * @param int $planID
     * @param int $portIndex
     */
    static public function storePortFromRequest(Request $request, int $planID, int $portIndex)
    {
        try {
            $part = PlanPartsModel::find($planID);
            if (!$part) abort(404);
            
            $ports = json_decode($part->ports) ?? [];
            
            $port = (object)[
                'surface' => $request->surface,
                'offset' => $request->offset,
                'width' => $request->width,
                'depth' => $request->depth,
            ];

            if ($portIndex == -1) {
                $portIndex = count($ports);
            }
            $ports[$portIndex] = $port;
            array_values($ports);
            $part->ports = json_encode($ports);
            $part->save();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param int $partID
     * @param int $portIndex
     */
    static public function deletePortByIndex(int $partID, int $portIndex)
    {
        try {
            $part = PlanPartsModel::find($partID);
            if (!$part) abort(404);
            
            $ports = json_decode($part->ports);
            if (isset($ports[$portIndex])) {
                array_splice($ports, $portIndex, 1);
                $part->ports = json_encode($ports);
                $part->save();
            }
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
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
                $plan = new PlanPartsModel();
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
            PlanPartsModel::truncate();

            // Рекурсивно заливаем новые записи
            $storeLevel($parts, null);

            // Нужно пересчитать максимальный уровень вложения структуры
            PlanPartsModel::calcAndStoreMaxLevel();

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
    
    /**
     * 
     * @param type $defaults
     * @return type
     */
    public function getBounds($defaults = null)
    {
        $bounds = $this->bounds ? json_decode($this->bounds) : (object)[];
        
        if (!isset($bounds->X)) $bounds->X = ($defaults && isset($defaults->X)) ? $defaults->X : 0;
        if (!isset($bounds->Y)) $bounds->Y = ($defaults && isset($defaults->Y)) ? $defaults->Y : 0;
        if (!isset($bounds->W)) $bounds->W = ($defaults && isset($defaults->W)) ? $defaults->W : 10;
        if (!isset($bounds->H)) $bounds->H = ($defaults && isset($defaults->H)) ? $defaults->H : 6;
        
        return $bounds;
    }
    
    /**
     * 
     * @return type
     */
    public function getBoundsRelativeParent()
    {
        $bounds = $this->getBounds();
        
        if ($this->id > 0) {
            $off = PlanPartsModel::parentOffset($this->parent_id);
            $bounds->X -= $off->X;
            $bounds->Y -= $off->Y;
        }
        
        return $bounds;
    }
    
    /**
     * 
     * @return type
     */
    public function getStyle()
    {
        $style = $this->style ? json_decode($this->style) : (object)[];
        
        if (!isset($style->pen_style)) $style->pen_style = 'solid';
        if (!isset($style->pen_width)) $style->pen_width = 1;
        if (!isset($style->fill)) $style->fill = 'background';
        if (!isset($style->name_dx)) $style->name_dx = 0;
        if (!isset($style->name_dy)) $style->name_dy = 0;     
            
        return $style;
    }
    
    /**
     * 
     * @param int $index
     * @param type $defaults
     * @return type
     */
    public function getPort(int $index, $defaults = null) 
    {
        $ports = json_decode($this->ports) ?? [];
        
        if (isset($ports[$index])) {
            $port = $ports[$index];
        } else {
            $port = (object)[];
        }
        
        if (!isset($port->surface)) $port->surface = ($defaults && isset($defaults->surface)) ? $defaults->surface : 'top';
        if (!isset($port->offset)) $port->offset = ($defaults && isset($defaults->offset)) ? $defaults->offset : 0;
        if (!isset($port->width)) $port->width = ($defaults && isset($defaults->width)) ? $defaults->width : 0.8;
        if (!isset($port->depth)) $port->depth = ($defaults && isset($defaults->depth)) ? $defaults->depth : 0.3;
        
        return $port;
    }
}