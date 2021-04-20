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
    public function index(int $hubID = null, $groupID = null) 
    {
        if (!\App\Http\Models\ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        $where = '';
        switch ($groupID) {
            case null:
                break;
            case 'empty':
                $where = ' and not exists(select 1 from plan_parts pp where v.group_id = pp.id)';
                break;
            default:
                $groupID = (int)$groupID;
                $ids = \App\Http\Models\PlanPartsModel::genIDsForGroupAtParent($groupID);
                if ($ids) {
                    $where = ' and v.group_id in ('.$ids.') ';
                }
                break;
        }
        
        $sql = 'select v.id,
                       v.typ,
                       v.name,
                       v.comm,
                       v.app_control,
                       v.value,
                       v.channel,
                       v.last_update,
                       (select p.name from plan_parts p where p.id = v.group_id) group_name,
                       exists(select 1 from core_variable_events e where e.variable_id = v.id) with_events
                  from core_variables v
                 where v.controller_id = '.$hubID.'
                '.$where.'
                order by v.name';
        
        $data = DB::select($sql);
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
            'data' => $data,
            'groupID' => $groupID,
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
                $this->validate($request, [
                    'controller_id' => 'required|numeric',
                    'name' => 'required|string|unique:core_variables,name,'.($id > 0 ? $id : ''),
                    'comm' => 'nullable|string',
                    'ow_id' => ($request->post('typ') === 'ow' ? 'required|numeric' : ''),
                    'value' => ($request->post('typ') === 'variable' ? 'required|numeric' : ''),
                ]);
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
                $item->name = $request->post('name');
                $item->comm = $request->post('comm');
                $item->channel = $request->post('channel') ?? 0;
                if ($request->post('value') !== null) {
                    $item->value = $request->post('value');
                }
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
                    'name' => '',
                    'comm' => '',
                    'group_id' => 1,
                    'app_control' => 0,
                    //'value' => 0,
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
                $data = config('firmware.channels.'.config('firmware.mmcu'));
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
