<?php

namespace App\Http\Controllers\Admin\Hubs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Session;
use Log;

class DevicesController extends Controller
{
    /**
     * This is an index route for displaying devices a list of the hub.
     * If the hub id does not exist, redirect to the owner route.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null, $groupID = null) 
    {        
        if (!\App\Http\Models\ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        if (!$groupID) {
            $groupID = Session::get('DEVICES_GROUP_ID');
            /*if (\App\Http\Models\PlanPartsModel::find($groupID)) {
                return redirect(route('admin.hub-devices', [$hubID, $groupID]));
            } else {
                $groupID = null;
            }*/
        }
        
        if (!$groupID) {
            $groupID = 'none';
        }
        
        $where = '';
        switch ($groupID) {
            case 'none':
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
        
        Session::put('HUB_INDEX_ID', $hubID);
        Session::put('DEVICES_GROUP_ID', $groupID);
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
            'data' => $data,
            'groupID' => $groupID,
        ]);
    }
    
    /**
     * Route to create or update device propertys.
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
                    'value' => 'nullable|numeric',
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
                $item->app_control = $request->post('app_control');
                $item->save();
                if ($request->post('value') !== null) {
                    \App\Http\Models\VariablesModel::setValue($item->id, $request->post('value'));
                }                
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
                    'group_id' => null,
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
            
            $groupPath = \App\Http\Models\PlanPartsModel::getPath($item->group_id, '/');
            
            return view('admin.hubs.devices.device-edit', [
                'item' => $item,
                'typs' => $typs,
                'groupPath' => $groupPath,
            ]);            
        }
    }
    
    /**
     * Route to delete the device by id.
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
     * Route for requesting a list of hubs by host id.
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
     * Route for requesting a list of host channels by id.
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
