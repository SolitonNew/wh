<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Log;

class ConfigurationController extends Controller
{
    /**
     * 
     * @param int $id
     * @return type
     */
    public function index(int $id = null) {
        if (!$id) {
            $item = \App\Http\Models\ControllersModel::orderBy('id', 'asc')->first();
            if ($item) {
                return redirect(route('configuration', $item->id));
            }
        }
        
        $sql = 'select d.id, 
                       c.name controller_name, 
                       "" rom,
                       d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7,
                       t.channels,
                       t.comm,
                       "" variables,
                       d.lost
                  from core_ow_devs d, core_ow_types t, core_controllers c
                 where d.controller_id = c.id
                   and d.rom_1 = t.code
                   and d.controller_id = "'.$id.'" 
                order by c.name, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7';
        $data = DB::select($sql);
        
        foreach($data as &$row) {
            $row->rom = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
                $row->rom_1, 
                $row->rom_2, 
                $row->rom_3, 
                $row->rom_4, 
                $row->rom_5, 
                $row->rom_6, 
                $row->rom_7
            );
            
            $row->variables = DB::select('select v.id, v.name, v.channel
                                            from core_variables v 
                                           where v.typ = "ow" 
                                             and v.ow_id = '.$row->id.'
                                          order by v.channel');
        }
        
        return view('admin.configuration.configuration', [
            'id' => $id,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return type
     */
    public function edit(Request $request, int $id) {
        if ($request->method() == 'POST') {
            try {
                $this->validate($request, [
                    'name' => 'string|required',
                    'comm' => 'string|nullable',
                    'rom' => 'numeric|required|unique:core_controllers,rom,'.($id > 0 ? $id : ''),
                ]);          
            } catch (\Illuminate\Validation\ValidationException $ex) {
                return response()->json($ex->validator->errors());
            }

            try {
                $item = \App\Http\Models\ControllersModel::find($id);
                if (!$item) {
                    $item = new \App\Http\Models\ControllersModel();
                }
                $item->name = $request->post('name');
                $item->is_server = $request->post('is_server') ? 1 : 0;
                $item->rom = $request->post('rom');
                $item->comm = $request->post('comm');
                $item->status = 0; 
                $item->save();
                
                return 'OK';
            } catch (\Exception $ex) {
                return response()->json([
                    'error' => [$ex->errorInfo],
                ]);
            }
        } else {
            $item = \App\Http\Models\ControllersModel::find($id);
            if (!$item) {
                $item = (object)[
                    'id' => -1,
                    'name' => '',
                    'is_server' => 0,
                    'rom' => '',
                    'comm' => '',
                    'status' => 0,
                ];
            }
            return view('admin.configuration.configuration-edit', [
                'item' => $item,
            ]);
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) {
        try {
            $item = \App\Http\Models\ControllersModel::find($id);
            $item->delete();            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function owInfo(int $id) {
        $sql = 'select d.id, 
                       c.name controller_name, 
                       "" rom,
                       d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7,
                       t.channels,
                       t.comm,
                       "" variables
                  from core_ow_devs d, core_ow_types t, core_controllers c
                 where d.controller_id = c.id
                   and d.rom_1 = t.code
                   and d.id = '.$id.'
                order by c.name, d.rom_1, d.rom_2, d.rom_3, d.rom_4, d.rom_5, d.rom_6, d.rom_7';
        $data = DB::select($sql);
        if (count($data)) {
            $item = $data[0];
        } else {
            abort(404);
        }
        
        $item->rom = sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X", 
            $item->rom_1, 
            $item->rom_2, 
            $item->rom_3, 
            $item->rom_4, 
            $item->rom_5, 
            $item->rom_6, 
            $item->rom_7
        );
        
        $sql = 'select v.id, v.name, v.channel
                  from core_variables v 
                 where v.typ = "ow" 
                   and v.ow_id = '.$item->id.'
                order by v.channel';
                
        $item->variables = DB::select($sql);
        
        return view('admin.configuration.configuration-ow-info', [
            'item' => $item,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function owDelete(int $id) {
        try {
            $item = \App\Http\Models\OwDevsModel::find($id);
            $item->delete();            
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @return type
     */
    public function runOwScan() {
        \App\Http\Models\PropertysModel::setRs485Command('OW SEARCH');
        $i = 0;
        while ($i++ < 500) { // 5 sec
            usleep(100000);
            $text = \App\Http\Models\PropertysModel::getRs485CommandInfo();
            if ($t = strpos($text, 'END_OW_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }
        return view('admin.configuration.configuration-ow-scan', [
            'data' => $text,
        ]);
    }
    
    /**
     * 
     * @return string
     */
    public function generateVarsForFreeDevs() {
        $devs = DB::select('select d.id, d.controller_id, t.channels, t.comm
                              from core_ow_devs d, core_ow_types t
                             where d.rom_1 = t.code');
        
        $vars = DB::select('select ow_id, channel from core_variables where typ = "ow"');
        
        try {
            foreach($devs as $dev) {
                foreach (explode(',', $dev->channels) as $chan) {
                    $find = false;
                    foreach($vars as $var) {
                        if ($var->ow_id == $dev->id && $var->channel && $var->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new \App\Http\Models\VariablesModel();
                        $item->controller_id = $dev->controller_id;
                        $item->typ = 'ow';
                        $item->direction = 0;
                        $item->name = 'temp for ow';
                        $item->comm = $dev->comm;
                        $item->ow_id = $dev->id;
                        $item->channel = $chan;
                        $item->save();
                        $item->name = 'ow_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
            return 'OK';
        } catch (\Exception $ex) {
            Log::info($ex);
            return 'ERROR';
        }
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public function configurationFirmware(int $id = null) {
        $text = '';
        try {
            $firmware = new \App\Library\Firmware();
            
            $firmware->generateConfig();
            
            $outs = [];
            if ($firmware->make($outs)) {
                $text = implode("\n", $outs);
            } else {
                $text = "ERROR \n".implode("\n", $outs);
            }
        } catch (\Exception $ex) {
            $text = "ERROR \n".$ex->getMessage();
        }
        
        return view('admin.configuration.configuration-firmware', [
            'data' => $text,
        ]);
    }
    
    /**
     * 
     */
    public function resetControllers() {
        try {
            \App\Http\Models\PropertysModel::setRs485Command('RESET');
            return 'OK';            
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
}
