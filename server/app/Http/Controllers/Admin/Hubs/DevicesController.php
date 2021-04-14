<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class DevicesController extends Controller
{
    /**
     * Индексный маршрут для отображения списка устройств хаба.
     * Если ИД хаба не существует делает переадресацию на заглавный маршрут.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) 
    {
        if (!\App\Http\Models\ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        $sql = 'select v.id,
                       v.typ,
                       v.direction,
                       v.name,
                       v.comm,
                       v.app_control,
                       v.value,
                       v.channel,
                       exists(select 1 from core_variable_events e where e.variable_id = v.id) with_events
                  from core_variables v
                 where v.controller_id = '.$hubID.'
                order by 2, 5';
        
        $data = DB::select($sql);
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
            'data' => $data,
        ]);
    }
    
    /**
     * Маршрут для создания/редактирования свойств устройства.
     * 
     * @param int $nubId
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $hubID, int $id) 
    {
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
                $item->typ = $request->post('typ');
                $item->ow_id = $request->post('ow_id');
                $item->direction = $request->post('direction');
                $item->name = $request->post('name');
                $item->comm = $request->post('comm');
                $item->channel = $request->post('channel') ?? 0;
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
                    'controller_id' => $hubID,
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
            
            return view('admin.hubs.devices.device-edit', [
                'item' => $item,
                'typs' => $typs,
            ]);            
        }
    }
    
    /**
     * Маршрут для удаления устройства по ИД
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        try {
            $item = \App\Http\Models\VariablesModel::find($id);
            if ($item) {
                $item->delete();
            }
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * Маршрут запроса списка хабов по ИД хоста.
     * 
     * @param int $hubID
     * @return type
     */
    public function hostList(int $hubID) 
    {
        $data = DB::select("select d.id, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7, d.rom_8,
                                   (select count(1)
                                      from core_variables v 
                                     where v.ow_id = d.id) num
                              from core_ow_devs d
                             where d.controller_id = $hubID
                            order by d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7, d.rom_8");
        return response()->json($data);
    }
    
    /**
     * Маршрут запроса списка каналов по типу хоста и его ИД.
     * 
     * @param string $typ [din, ow, variable]
     * @param int $hostID
     * @return type
     */
    public function hostChannelList(string $typ, int $hostID = null) 
    {
        switch ($typ) {
            case 'din':
                $data = [
                    'R1', 
                    'R2', 
                    'R3',
                    'R4',
                ];
                break;
            case 'ow':
                if ($hostID) {
                    $c = DB::select('select t.channels
                                       from core_ow_devs d, core_ow_types t
                                      where d.rom_1 = t.code
                                        and d.id = '.$hostID);
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
}
