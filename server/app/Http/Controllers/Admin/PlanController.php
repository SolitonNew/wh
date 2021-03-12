<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use Log;

class PlanController extends Controller
{
    public function index(int $id = -1) {
        if ($id == -1) {
            $first = \App\Http\Models\PlanPartsModel::whereParentId(-1)
                        ->orderBy('order_num', 'asc')
                        ->first();
            if ($first) {
                return redirect(route('plan', $first->id));
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
    
    public function edit(Request $request, int $id, int $p_id = -1) {
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
                    $bounds = json_decode($item->BOUNDS);
                    if ($bounds) {
                        $dx = $request->post('X') - $bounds->X;
                        $dy = $request->post('Y') - $bounds->Y;
                    }
                }
                
                $item->PARENT_ID = $request->post('parent_id');
                $item->NAME = $request->post('name');
                $item->BOUNDS = json_encode([
                    'X' => $request->post('X'),
                    'Y' => $request->post('Y'),
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
            
            if (!$item->bounds) {
                $item->bounds = json_encode([
                    'X' => 0,
                    'Y' => 0,
                    'W' => 10,
                    'H' => 6,
                ]);
            }
            
            return view('admin.plan.plan-edit', [
                'item' => $item,
                'itemBounds' => json_decode($item->bounds),
            ]);
        }
    }
    
    /**
     * 
     * @param type $id
     * @return string
     */
    public function delete($id) {
        try {
            $item = \App\Http\Models\PlanPartsModel::find($id);
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    public function moveChilds(Request $request, int $id) {
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
    
    public function order(Request $request, int $id) {
        if ($request->method() == 'POST') {
            $ids = explode(',', $request->post('orderIds'));
            for ($i = 0; $i < count($ids); $i++) {
                $item = \App\Http\Models\PlanPartsModel::find($ids[$i]);
                $item->order_num = $i + 1;
                $item->save();
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
}
