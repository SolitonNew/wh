<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class VariablesController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index(int $partID = 1) {
        $where = '';
        if ($partID > 1) {
            $ids = \App\Http\Models\PlanPartsModel::genIDsForGroupAtParent($partID);
            $where = 'and v.group_id in ('.$ids.')';
        }
        
        $sql = 'select v.id,
                       c.name controller_name,
                       v.typ,
                       v.direction,
                       v.name,
                       v.comm,
                       v.app_control,
                       v.value,
                       v.channel,
                       exists(select 1 from core_variable_events e where e.variable_id = v.id) with_events
                  from core_variables v, core_controllers c
                 where v.controller_id = c.id
                   '.$where.'
                order by 2, v.name';
        
        $data = DB::select($sql);
        
        return view('admin.variables.variables', [
            'partID' => $partID,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\VariablesModel::find($id);
        
        if ($request->method() == 'POST') {
            try {
                $rules = [];
                $rules['controller_id'] = 'required|numeric';
                $rules['name'] = 'required|string|unique:core_variables,name,'.($id > 0 ? $id : '');
                $rules['comm'] = 'required|string';
                
                if ($request->post('typ') == 'ow') {
                    $rules['ow_id'] = 'required|numeric';
                }
                
                if ($request->post('rom') == 'variable' || $request->post('direction') == '0') {
                    $rules['value'] = 'required|numeric';
                }
                
                $this->validate($request, $rules);
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            if (!$item) {
                $item = new \App\Http\Models\VariablesModel();
            }
            
            try {
                $item->controller_id = $request->post('controller_id');
                $item->rom = $request->post('typ');
                $item->ow_id = $request->post('ow_id');
                $item->direction = $request->post('direction');
                $item->name = $request->post('name');
                $item->comm = $request->post('comm');
                $item->channel = $request->post('channel');
                $item->value = $request->post('value');
                $item->group_id = $request->post('group_id');
                $item->app_control = $request->post('app_control');
                $item->save();
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->errorInfo],
                ]);
            }
        } else {
            if (!$item) {
                $item = (object)[
                    'id' => -1,
                    'controller_id' => -1,
                    'typ' => 'ow',
                    'ow_id' => '',
                    'direction' => 0,
                    'name' => '',
                    'comm' => '',
                    'group_id' => 1,
                    'app_control' => 0,
                    'value' => 0,
                    'channel' => 0,
                ];
            }
            
            $typs = [
                'din' => 'din',
                'ow' => 'ow',
                'variable' => 'variable',
            ];
            
            return view('admin.variables.variable-edit', [
                'item' => $item,
                'typs' => $typs,
            ]);            
        }
    }
    
    /**
     * Список OW устройств для диалога свойств переменной
     * 
     * @param int $controller
     * @return type
     */
    public function owList(int $controller = -1) {
        $data = DB::select("select d.id, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7, d.rom_8,
                                   (select count(1)
                                      from core_variables v 
                                     where v.ow_id = d.id) num
                              from core_ow_devs d
                             where d.controller_id = $controller
                            order by d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7, d.rom_8");
        return response()->json($data);
    }
    
    /**
     * Список каналов для диалога свойств переменной
     * 
     * @param type $rom
     * @param int $ow_id
     * @return type
     */
    public function channelList($rom, int $ow_id = null) {
        switch ($rom) {
            case 'din':
                $data = [
                    'R1', 
                    'R2', 
                    'R3',
                    'R4',
                ];
                break;
            case 'ow':
                if ($ow_id) {
                    $c = DB::select('select t.channels
                                       from core_ow_devs d, core_ow_types t
                                      where d.rom_1 = t.code
                                        and d.id = '.$ow_id);
                    if (count($c)) {
                        $data = explode(',', $c[0]->channels);
                    } else {
                        $data = [];
                    }
                } else {
                    $data = [];
                }
                break;
            default:
                $data = [];
        }
        return response()->json($data);
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) {
        $item = \App\Http\Models\VariablesModel::find($id);
        if ($item) {
            $item->delete();
            return 'OK';
        }
        
        return 'ERROR';
    }
}
