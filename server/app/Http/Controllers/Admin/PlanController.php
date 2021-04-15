<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Log;

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
        
        $data = \App\Http\Models\PlanPartsModel::generateTree($id);
        
        foreach($data as &$row) {
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
        }
        
        return view('admin.plan.plan', [
            'partID' => $id,
            'data' => $data,
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
                $dx = 0;
                $dy = 0;                
                if (!$item) {
                    $item = new \App\Http\Models\PlanPartsModel();
                } else {
                    $bounds = json_decode($item->bounds);
                    if ($bounds) {
                        $dx = $request->post('X') - $bounds->X;
                        $dy = $request->post('Y') - $bounds->Y;
                    }
                }
                
                $item->parent_id = $request->post('parent_id');
                $item->name = $request->post('name');
                
                $off = $item->parentOffset();
                $item->bounds = json_encode([
                    'X' => $request->post('X') + $off->X,
                    'Y' => $request->post('Y') + $off->Y,
                    'W' => $request->post('W'),
                    'H' => $request->post('H'),
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
                ];
            }
            
            if ($item->bounds) {
                $itemBounds = json_decode($item->bounds);
                if ($item instanceof \App\Http\Models\PlanPartsModel) {
                    $off = $item->parentOffset();
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
            
            return view('admin.plan.plan-edit', [
                'item' => $item,
                'itemBounds' => $itemBounds,
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
            return 'OK';
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
}
