<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use DB;

class PlanController extends Controller
{
    /**
     * Индексный маршрут для работы с планом системы.
     * 
     * @param int $id
     * @return type
     */
    public function index(int $id = null) 
    {
        if (!$id) {
            $first = \App\Http\Models\PlanPartsModel::whereParentId(null)
                        ->orderBy('order_num', 'asc')
                        ->first();
            if ($first) {
                return redirect(route('admin.plan', $first->id));
            }
        }
        
        // Читаем список записей плана
        $data = \App\Http\Models\PlanPartsModel::generateTree($id);
        foreach($data as $row) {
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
            $row->pen_color = isset($v->pen_color) ? $v->pen_color : '#000000';
            $row->fill_color = isset($v->fill_color) ? $v->fill_color : '#EEEEEE';
        }
        
        // Читаем список устройств
        $devices = [];
        foreach(\App\Http\Models\VariablesModel::get() as $device) {
            $part = false;
            foreach($data as $row) {
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
        
        return view('admin.plan.plan', [
            'partID' => $id,
            'data' => $data,
            'devices' => $devices,
        ]);
    }
    
    /**
     * Маршрут создать/изменить запись плана.
     * 
     * @param Request $request
     * @param int $id
     * @param int $p_id
     * @return string
     */
    public function edit(Request $request, int $id, int $p_id = -1) 
    {
        $item = \App\Http\Models\PlanPartsModel::find($id);
        
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'name' => 'required|string',
                    'X' => 'required|numeric',
                    'Y' => 'required|numeric',
                    'W' => 'required|numeric',
                    'H' => 'required|numeric',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                $off = \App\Http\Models\PlanPartsModel::parentOffset($request->post('parent_id'));
                
                $dx = 0;
                $dy = 0;                
                if (!$item) {
                    $item = new \App\Http\Models\PlanPartsModel();
                } else {
                    $bounds = json_decode($item->bounds);
                    if ($bounds) {
                        $dx = $request->post('X') + $off->X - $bounds->X;
                        $dy = $request->post('Y') + $off->Y - $bounds->Y;
                    }
                }
                
                $item->parent_id = $request->post('parent_id');
                $item->name = $request->post('name');
                
                $item->bounds = json_encode([
                    'X' => $request->post('X') + $off->X,
                    'Y' => $request->post('Y') + $off->Y,
                    'W' => $request->post('W'),
                    'H' => $request->post('H'),
                ]);
                $item->style = json_encode([
                    'pen_style' => $request->post('pen_style'),
                    'pen_width' => $request->post('pen_width'),
                    'pen_color' => $request->post('pen_color'),
                    'fill_color' => $request->post('fill_color'),
                ]);
                $item->save();
                
                if (($dx != 0) || ($dy != 0)) {
                    $item->moveChilds($dx, $dy);
                }
                
                if ($id == -1) {
                    $item->order_num = $item->id;
                    $item->save();
                }
                
                // Нужно пересчитать максимальный уровень вложения структуры
                \App\Http\Models\PlanPartsModel::calcAndStoreMaxLevel();
                
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->getMessage()],
                ]);
            }
        } else {
            if (!$item) {
                $item = (object)[
                    'id' => -1,
                    'name' => '',
                    'parent_id' => $p_id,
                    'order_num' => null,
                    'bounds' => null,
                    'style' => null,
                ];
            }
            
            if ($item->bounds) {
                $itemBounds = json_decode($item->bounds);
                if ($item instanceof \App\Http\Models\PlanPartsModel) {
                    $off = \App\Http\Models\PlanPartsModel::parentOffset($item->parent_id);
                    $itemBounds->X -= $off->X;
                    $itemBounds->Y -= $off->Y;
                }
            } else {
                $itemBounds = (object)[
                    'X' => 0,
                    'Y' => 0,
                    'W' => 10,
                    'H' => 6,
                ];
            }
            
            if ($item->style) {
                $itemStyle = json_decode($item->style);
            } else {
                $itemStyle = (object)[];
            }
            
            if (!isset($itemStyle->pen_style)) $itemStyle->pen_style = 'solid';
            if (!isset($itemStyle->pen_width)) $itemStyle->pen_width = 1;
            if (!isset($itemStyle->pen_color)) $itemStyle->pen_color = '#000000';
            if (!isset($itemStyle->fill_color)) $itemStyle->fill_color = '#EEEEEE';
            
            return view('admin.plan.plan-edit', [
                'item' => $item,
                'itemBounds' => $itemBounds,
                'itemStyle' => $itemStyle,
            ]);
        }
    }
    
    /**
     * Маршрут для удаления записи плана.
     * 
     * @param type $id
     * @return string
     */
    public function delete($id) 
    {
        try {
            $item = \App\Http\Models\PlanPartsModel::find($id);
            $item->delete();
            
            // Нужно пересчитать максимальный уровень вложения структуры
            \App\Http\Models\PlanPartsModel::calcAndStoreMaxLevel();
            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Маршрут для клонирования записи плана.
     * Делает копию записи но изменяет координаты новой записи с учетом 
     * входного параметра $direction таким образом, что бы новая запись 
     * прилегала к исходной.
     * 
     * @param int $id
     * @param string $direction
     * @return string
     */
    public function planClone(int $id, string $direction) 
    {
        try {
            $part = \App\Http\Models\PlanPartsModel::find($id);
            if ($part) {
                $new_part = new \App\Http\Models\PlanPartsModel();
                
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
                
                return 'OK';
            }            
            return 'ERROR';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Маршрут для отображения окна перемещения к другому подчиненному 
     * записи плана.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function moveChilds(Request $request, int $id) 
    {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'DX' => 'required|numeric',
                    'DY' => 'required|numeric',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            try {
                $item = \App\Http\Models\PlanPartsModel::find($id);
                $item->moveChilds($request->post('DX'), $request->post('DY'));
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->getMessage()],
                ]);
            }
        } else {
            return view('admin.plan.plan-move-childs', [
                'partID' => $id,
            ]);
        }
    }
    
    /**
     * Маршрут для отображения окна упорядолчивания записей плана.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function order(Request $request, int $id) 
    {
        if ($request->method() == 'POST') {
            $ids = explode(',', $request->post('orderIds'));
            for ($i = 0; $i < count($ids); $i++) {
                $item = \App\Http\Models\PlanPartsModel::find($ids[$i]);
                if ($item) {
                    $item->order_num = $i + 1;
                    $item->save();
                }
            }
            return 'OK';
        } else {
            $data = DB::select("select p.* from plan_parts p where p.parent_id = $id order by p.order_num");
            
            return view('admin.plan.plan-order', [
                'partID' => $id,
                'data' => $data,
            ]);
        }
    }
    
    /**
     * Маршрут для импорта плана из файла.
     * GET: отображает окно для выбора файла.
     * POST: Выполняет нужные манипуляции с данными и полученым файлом.
     * 
     * @param Request $request
     * @return string
     */
    public function planImport(Request $request) 
    {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'file' => 'file|required',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->errors());
            }
            
            $storeLevel = function ($level, $parentID) use (&$storeLevel) {
                $i = 1;
                foreach($level as $item) {
                    $plan = new \App\Http\Models\PlanPartsModel();
                    $plan->id = $item->id;
                    $plan->parent_id = $parentID;
                    $plan->name = $item->name;
                    $plan->bounds = $item->bounds;
                    $plan->style = $item->style;
                    $plan->order_num = $i++;
                    $plan->save();                    
                    $storeLevel($item->childs, $item->id);
                }
            };
            
            try {
                // Принимаем файл
                $json = file_get_contents($request->file('file'));
                
                // Декодируем
                $parts = json_decode($json);
                
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
        } else {
            return view('admin.plan.plan-import', []);
        }
    }
    
    /**
     * Маршрут для экспорта плана системы.
     * Данные плана собираются в виде вложенных (древовидных) объектов и 
     * серриализируются в виде json строки.
     * 
     * @return type
     */
    public function planExport() 
    {
        $parts = \App\Http\Models\PlanPartsModel::orderBy('order_num', 'asc')->get();
        
        $loadLevel = function ($parentID) use (&$loadLevel, $parts) {
            $res = [];
            foreach($parts as $part) {
                if ($part->parent_id == $parentID) {
                    $res[] = (object)[
                        'id' => $part->id,
                        'name' => $part->name,
                        'bounds' => $part->bounds,
                        'style' => $part->style,
                        'childs' => $loadLevel($part->id),
                    ];
                }
            }
            return $res;
        };
        
        $file = json_encode($loadLevel(null));
        
        return response($file, 200, [
            'Content-Length' => strlen($file),
            'Content-Disposition' => 'attachment; filename="'.\Carbon\Carbon::now()->format('Ymd_His').'_plan.json"',
            'Pragma' => 'public',
        ]);
    }
    
    /**
     * Маршрут для установки/переустановки связи между устройством и 
     * фрагментом плана, а также определения положения устройства на плане.
     * 
     * @param Request $request
     * @param int $planID
     * @param int $deviceID
     * @return string
     * @throws Exception
     */
    public function linkDevice(Request $request, int $planID, int $deviceID = -1) 
    {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'offset' => 'numeric|required',
                    'cross' => 'numeric|required',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->errors());
            }
            
            try {
                $deviceID = $request->post('device');
                $device = \App\Http\Models\VariablesModel::find($deviceID);
                if ($device) {
                    $position = (object)[
                        'surface' => $request->post('surface'),
                        'offset' => $request->post('offset'),
                        'cross' => $request->post('cross'),
                    ];
                    $device->group_id = $planID;
                    $device->position = json_encode($position);
                    $device->save();                    
                    return 'OK';
                } else {
                    throw new Exception('Device not found');
                }
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->getMessage()],
                ]);
            }
        } else {
            // Данные по положению устройства
            $device = \App\Http\Models\VariablesModel::find($deviceID);
            if ($device) {
                $position = json_decode($device->position) ?? (object)[];
            } else {
                $position = (object)[
                    'surface' => 'top',
                    'offset' => 0,
                    'cross' => 0,
                ];
            }
            
            if (!isset($position->surface)) $position->surface = 'top';
            if (!isset($position->offset)) $position->offset = 0;
            if (!isset($position->cross)) $position->cross = 0;
            
            // Список устройств с информацией по присоединению к комнатам
            $sql = "select v.*
                      from core_variables v
                    order by v.name";
            $devices = DB::select($sql);
            
            foreach($devices as $device) {
                $path = \App\Http\Models\PlanPartsModel::getPath($device->group_id, '/');
                if ($path) {
                    $device->inPlan = true;
                    $device->label = '['.$path.'] ';
                } else {
                    $device->inPlan = false;
                    $device->label = '';
                }
                $device->label .= $device->name;
                $app_control = \App\Http\Models\VariablesModel::decodeAppControl($device->app_control);
                $device->label .= ' '.$app_control->label;
            }
            
            usort($devices, function ($item1, $item2) {
                return $item1->inPlan > $item2->inPlan;
            });
            
            // Параметры комнаты
            $part = \App\Http\Models\PlanPartsModel::find($planID);
            if ($part && $part->bounds) {
                $partBounds = json_decode($part->bounds);
            } else {
                $partBounds = (object)[
                    'X' => 0,
                    'Y' => 0,
                    'W' => 5,
                    'H' => 5,
                ];
            }
            
            return view('admin.plan.plan-link-device', [
                'planID' => $planID,
                'deviceID' => $deviceID,
                'position' => $position,
                'devices' => $devices,
                'partBounds' => $partBounds,
            ]);
        }
    }
    
    /**
     * Маршрут для открепления устройства от фрагмента плана.
     * 
     * @param int $deviceID
     * @return string
     */
    public function unlinkDevice(int $deviceID) 
    {
        try {
            $device = \App\Http\Models\VariablesModel::find($deviceID);
            if ($device) {
                $device->group_id = -1;
                $device->position = null;
                $device->save();
            }
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
