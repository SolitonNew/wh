<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HubsController extends Controller
{
    /**
     * Индексный маршрут.
     * Если есть хотя бы один хаб делает переадресацию на страницу devices
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) {
        if (!$hubID) {
            $hubID = \App\Http\Models\ControllersModel::orderBy('name', 'asc')->first();
            if ($hubID) {
                $hubID = $hubID->id;
            } else {
                $hubID = null;
            }
        }
        
        if ($hubID) {
            return redirect(route('admin.hub-devices', [$hubID]));
        } else {
            return view('admin/hubs/hubs', [
                'hubID' => $hubID,
            ]);
        }
    }
    
    /**
     * Маршрут создания/редактирования записи хаба.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\ControllersModel::find($id);

        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'name' => 'string|required',
                    'comm' => 'string|nullable',
                    'rom' => 'numeric|required|unique:core_controllers,rom,'.($id > 0 ? $id : ''),
                ]);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->errors());
            }
            
            try {
                if (!$item) {
                    $item = new \App\Http\Models\ControllersModel();
                }
                
                $item->name = $request->post('name');
                $item->rom = $request->post('rom');
                $item->comm = $request->post('comm');
                $item->save();
                
            } catch (\Exception $ex) {
                return response()->json([
                    'errors' => $ex->getMessage(),
                ]);
            }
            
            return 'OK';
        } else {
            if (!$item) {
                $item = (object)[
                    'id' => -1,
                    'name' => '',
                    'rom' => null,
                    'comm' => '',
                    'status' => 1,
                ];
            }
            
            return view('admin/hubs/hub-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * Маршрут удаления хаба по ИД
     * 
     * @param int $id
     * @return type
     */
    public function delete(int $id) {
        try {
            $item = \App\Http\Models\ControllersModel::find($id);
            if (!$item) {
                return abort(404);
            }
            $item->delete();
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }
}
