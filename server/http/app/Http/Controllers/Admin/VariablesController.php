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
            $where = 'and v.GROUP_ID in ('.$ids.')';
        }
        
        $sql = 'select v.ID,
                       c.NAME CONTROLLER_NAME,
                       v.ROM,
                       v.DIRECTION,
                       v.NAME,
                       v.COMM,
                       v.APP_CONTROL,
                       v.VALUE,
                       v.CHANNEL
                  from core_variables v, core_controllers c
                 where v.controller_id = c.id
                   '.$where.'
                order by 2, v.name';
        
        $data = DB::select($sql);
        
        return view('admin.variables', [
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
                $this->validate($request, [
                    'NAME' => 'required|string|unique:core_variables,NAME,'.($id > 0 ? $id : ''),
                    'COMM' => 'required|string',
                ]);
                if ($request->post('ROM') == 'variable' || $request->post('DIRECTION') == '0') {
                    $this->validate($request, [
                        'VALUE' => 'required|numeric',
                    ]);
                }
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }
            
            if (!$item) {
                $item = new \App\Http\Models\VariablesModel();
            }
            
            try {
                $item->CONTROLLER_ID = $request->post('CONTROLLER_ID');
                $item->ROM = $request->post('ROM');
                $item->OW_ID = $request->post('OW_ID');
                $item->DIRECTION = $request->post('DIRECTION');
                $item->NAME = $request->post('NAME');
                $item->COMM = $request->post('COMM');
                $item->CHANNEL = $request->post('CHANNEL');
                $item->VALUE = $request->post('VALUE');
                $item->GROUP_ID = $request->post('GROUP_ID');
                $item->APP_CONTROL = $request->post('APP_CONTROL');
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
                    'ID' => -1,
                    'CONTROLLER_ID' => -1,
                    'ROM' => 'ow',
                    'OW_ID' => '',
                    'DIRECTION' => 0,
                    'NAME' => '',
                    'COMM' => '',
                    'GROUP_ID' => 1,
                    'APP_CONTROL' => 0,
                    'VALUE' => 0,
                    'CHANNEL' => 0,
                ];
            }
            
            $typs = [
                'ow' => 'ow',
                'pyb' => 'pyb',
                'variable' => 'variable',
            ];
            
            return view('admin.variable-edit', [
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
    public function owList(int $controller) {
        $data = DB::select("select d.ID, d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7, d.ROM_8,
                                   (select count(1)
                                      from core_variables v 
                                     where v.OW_ID = d.ID) NUM
                              from core_ow_devs d
                             where d.CONTROLLER_ID = $controller
                            order by d.ROM_1, d.ROM_2, d.ROM_3, d.ROM_4, d.ROM_5, d.ROM_6, d.ROM_7, d.ROM_8");
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
            case 'pyb':
                $data = [
                    'X1', 
                    'X2', 
                    'X3',
                    'X4',
                    'X5', 
                    'X6',
                    'X7',
                    'X8',
                    'X9',
                    'X10',
                    'X11',
                    'X12',
                    'Y1',
                    'Y2',
                    'Y3',
                    'Y4',
                    'Y5',
                    'Y6',
                    'Y7',
                    'Y8'];
                break;
            case 'ow':
                if ($ow_id) {
                    $c = DB::select('select t.CHANNELS
                                       from core_ow_devs d, core_ow_types t
                                      where d.ROM_1 = t.CODE
                                        and d.ID = '.$ow_id);
                    if (count($c)) {
                        $data = explode(',', $c[0]->CHANNELS);
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
