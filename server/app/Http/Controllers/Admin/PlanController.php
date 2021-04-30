<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use DB;
use Session;

class PlanController extends Controller
{
    /**
     * This is the index route for the system plan page to work.
     * 
     * @param int $id
     * @return type
     */
    public function index(int $id = null) 
    {
        if (!$id) {
            $id = Session::get('PLAN_INDEX_ID');
            if (\App\Http\Models\PlanPartsModel::find($id)) {
                return redirect(route('admin.plan', $id));
            }
            $id = null;
        }
        
        if (!$id) {
            $first = \App\Http\Models\PlanPartsModel::whereParentId(null)
                        ->orderBy('order_num', 'asc')
                        ->first();
            if ($first) {
                return redirect(route('admin.plan', $first->id));
            }
        }
        
        // Load plan records
        $ports = [];
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
        
        Session::put('PLAN_INDEX_ID', $id);
        
        return view('admin.plan.plan', [
            'partID' => $id,
            'data' => $data,
            'ports' => $ports,
            'devices' => $devices,
        ]);
    }
    
    /**
     * Route to create or update plan entries.
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
                    'pen_width' => 'nullable|numeric',
                    'name_dx' => 'nullable|numeric',
                    'name_dy' => 'nullable|numeric',
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
                    'fill' => $request->post('fill'),
                    'name_dx' => $request->post('name_dx') ?? 0,
                    'name_dy' => $request->post('name_dy') ?? 0,
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
            if (!isset($itemStyle->fill)) $itemStyle->fill = 'background';
            if (!isset($itemStyle->name_dx)) $itemStyle->name_dx = 0;
            if (!isset($itemStyle->name_dy)) $itemStyle->name_dy = 0;
            
            return view('admin.plan.plan-edit', [
                'item' => $item,
                'itemBounds' => $itemBounds,
                'itemStyle' => $itemStyle,
            ]);
        }
    }
    
    /**
     * Route to delete plan entries.
     * 
     * @param type $id
     * @return string
     */
    public function delete($id) 
    {
        try {
            $item = \App\Http\Models\PlanPartsModel::find($id);
            $item->delete();
            
            // Recalc max level
            \App\Http\Models\PlanPartsModel::calcAndStoreMaxLevel();
            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Route to clone plan entries.
     * Makes a copy of the record but changes the coordinates of the new 
     * record given the $ direction input parameter so that the new record 
     * is adjacent to the original.
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
     * Route to displaying the plan owner change window.
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
     * Route to displaying the plan ordering window.
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
     * Route to move of the plan entries by id.
     * 
     * @param Request $request
     * @param int $id
     * @param float $newX
     * @param float $newY
     * @return string
     */
    public function move(Request $request, int $id, float $newX, float $newY) 
    {
        try {
            $item = \App\Http\Models\PlanPartsModel::find($id);
            if ($item) {
                $bounds = json_decode($item->bounds);
                $bounds->X = $newX;
                $bounds->Y = $newY;
                $item->bounds = json_encode($bounds);
                $item->save();
            }
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Route to resize of the plan entries.
     * 
     * @param Request $request
     * @param int $id
     * @param float $newW
     * @param float $newH
     * @return string
     */
    public function size(Request $request, int $id, float $newW, float $newH)
    {
        try {
            $item = \App\Http\Models\PlanPartsModel::find($id);
            if ($item) {
                $bounds = json_decode($item->bounds);
                $bounds->W = $newW;
                $bounds->H = $newH;
                $item->bounds = json_encode($bounds);
                $item->save();
            }
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Route to import plan from file.
     * GET: displaying window for choise file.
     * POST: run import.
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
            
            try {
                $data = file_get_contents($request->file('file'));
                \App\Http\Models\PlanPartsModel::importFromString($data);
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
     * Route to export plan entries to file.
     * Маршрут для экспорта плана системы.
     * The plan data is collected as nested (tree-like) objects and serialized 
     * as a json string.
     * 
     * @return type
     */
    public function planExport() 
    {
        $data = \App\Http\Models\PlanPartsModel::exportToString();
        
        return response($data, 200, [
            'Content-Length' => strlen($data),
            'Content-Disposition' => 'attachment; filename="'.\Carbon\Carbon::now()->format('Ymd_His').'_plan.json"',
            'Pragma' => 'public',
        ]);
    }
    
    /**
     * Route fro binding the device to plan entries and determines the 
     * position of the device on the plan.
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
            // Device position data
            $device = \App\Http\Models\VariablesModel::find($deviceID);
            if ($device) {
                $position = json_decode($device->position) ?? (object)[];
            } else {
                $position = (object)[
                    'surface' => $request->get('surface') ?? 'top',
                    'offset' => $request->get('offset') ?? 0,
                    'cross' => $request->get('cross') ?? 0,
                ];
            }
            
            if (!isset($position->surface)) $position->surface = 'top';
            if (!isset($position->offset)) $position->offset = 0;
            if (!isset($position->cross)) $position->cross = 0;
            
            // Generation of data for a single device or a list of devices.
            $device = (object)[];
            $devices = [];
            
            if ($deviceID == -1) {
                $sql = "select v.*
                          from core_variables v
                         where not exists(select 1 from plan_parts p where p.id = v.group_id)
                        order by v.name";
                $devices = DB::select($sql);
                
                foreach($devices as $dev) {
                    $dev->label = $dev->name.' '.($dev->comm);
                    $app_control = \App\Http\Models\VariablesModel::decodeAppControl($dev->app_control);
                    $dev->label .= ' '."'$app_control->label'";
                }
            } else {
                $device = \App\Http\Models\VariablesModel::find($deviceID);
                if (!$device) abort(404);
                $device->label = $device->name.' '.($device->comm);
                $app_control = \App\Http\Models\VariablesModel::decodeAppControl($device->app_control);
                $device->label .= ' '."'$app_control->label'";
            }
                        
            // Room settings
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
                'planPath' => \App\Http\Models\PlanPartsModel::getPath($planID, ' / '),
                'device' => $device,
                'devices' => $devices,
                'position' => $position,
                'partBounds' => $partBounds,
            ]);
        }
    }
    
    /**
     * Route to remove the device from the plan etries.
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
    
    /**
     * This route is used to add or update the port item of the plan_part item.
     * 
     * @param Request $request
     * @param int $planID
     * @param int $portID
     * @return type
     */
    public function portEdit(Request $request, int $planID, int $portIndex = -1) 
    {
        $part = \App\Http\Models\PlanPartsModel::find($planID);
        $ports = json_decode($part->ports) ?? [];
        
        if (isset($ports[$portIndex])) {
            $position = $ports[$portIndex];
        } else {
            $position = (object)[];
        }
        
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'offset' => 'numeric|required',
                    'width' => 'numeric|required',
                    'depth' => 'numeric|required',
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->errors());
            }
            
            try {
                $position->surface = $request->post('surface');
                $position->offset = $request->post('offset');
                $position->width = $request->post('width');
                $position->depth = $request->post('depth');
                if ($portIndex == -1) {
                    $portIndex = count($ports);
                }
                $ports[$portIndex] = $position;
                array_values($ports);
                $part->ports = json_encode($ports);
                $part->save();
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->getMessage()],
                ]);
            }
            return 'OK';
        } else {    
            if (!isset($position->surface)) $position->surface = $request->get('surface') ?? 'top';
            if (!isset($position->offset)) $position->offset = $request->get('offset') ?? 0;
            if (!isset($position->width)) $position->width = $request->get('width') ?? 0.8;
            if (!isset($position->depth)) $position->depth = $request->get('depth') ?? 0.3;
            
            // Room settings
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
            
            return view('admin.plan.plan-port-edit', [
                'planID' => $planID,
                'portIndex' => $portIndex,
                'partBounds' => $partBounds,
                'position' => $position,
            ]);
        }
    }
    
    /**
     * This route is used to delete the port element of the plan_parts item.
     * 
     * @param int $planID
     * @param int $portIndex
     * @return string
     */
    public function portDelete(int $planID, int $portIndex) 
    {
        try {
            $plan = \App\Http\Models\PlanPartsModel::find($planID);
            if ($plan) {
                $ports = json_decode($plan->ports);
                if (isset($ports[$portIndex])) {
                    array_splice($ports, $portIndex, 1);
                    $plan->ports = json_encode($ports);
                    $plan->save();
                }
            }
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
}
