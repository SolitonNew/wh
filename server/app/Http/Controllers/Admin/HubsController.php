<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Log;
use Lang;
use Session;

class HubsController extends Controller
{
    /**
     * This is index route.
     * If the hub exists, to redirect to the device page.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) 
    {   
        if (!$hubID) {
            $hubID = Session::get('HUB_INDEX_ID');
            if (\App\Http\Models\ControllersModel::find($hubID)) {
                //
            } else {
                $hubID = null;
            }
        }
        
        if (!$hubID) {
            $hubID = \App\Http\Models\ControllersModel::orderBy('rom', 'asc')->first();
            if ($hubID) {
                $hubID = $hubID->id;
            } else {
                $hubID = null;
            }
        }
        
        if ($hubID) {
            Session::put('HUB_INDEX_ID', $hubID);
            return redirect(route('admin.hub-devices', [$hubID]));
        } else {
            return view('admin/hubs/hubs', [
                'hubID' => $hubID,
            ]);
        }
    }
    
    /**
     * Route to create or update a hub property.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function edit(Request $request, int $id) 
    {
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
            
            // Перезапускаем rs485-demon
            $this->_restartRs485Demon();
            
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
     * Route to delete the hub by id.
     * 
     * @param int $id
     * @return type
     */
    public function delete(int $id) 
    {
        try {
            $item = \App\Http\Models\ControllersModel::find($id);
            if (!$item) {
                return abort(404);
            }
            $item->delete();
            
            // Перезапускаем rs485-demon
            $this->_restartRs485Demon();
            
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }
    
    /**
     * This route scans child hosts.
     * Returns a view with scan dialog report.
     * 
     * @return type
     */
    public function hubsScan() 
    {
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
        
        // Сразу же запускаем генератор устройств
        // Если устройства небыло - он создаст
        $this->_generateDevs();
        // --------------------------------------
        
        return view('admin.hubs.hubs-scan', [
            'data' => $text,
        ]);
    }
    
    /**
     * This method creted devices entries on each channel if the channel 
     * does not exists.
     * 
     * @return string
     */
    public function _generateDevs() 
    {
        $channelControl = [
            1 => ['R1', 'R2', 'R3', 'R4'],    // Light
            2 => ['LEFT', 'RIGHT'],           // Switch
            //3 => [],                          // Socket
            4 => ['T', 'TEMP'],               // Termometr
            //5 => [],                          // Termostat
            //6 => [],                          // Videcam
            7 => ['F1', 'F2', 'F3', 'F4'],    // Venting
            8 => ['P1', 'P2', 'P3', 'P4'],    // Motion sensor
            //9 => [],                          // Leakage sensor
            10 => ['H'],                      // Humidity
            11 => ['CO'],                     // Gas sensor
            //12 => [],                       // Door sensor
            //13 => [],                       // Atm. pressure
            14 => ['AMP'],                    // Currency sensor
        ];     
        
        $decodeChannel = function ($channel) use ($channelControl) {
            foreach($channelControl as $key => $val) {
                if (in_array($channel, $val)) {
                    return $key;
                }
            }
            return -1;
        };
        
        // Generation of devices by channel
        $din_channels = config('firmware.channels.'.config('firmware.mmcu'));
        $vars = DB::select('select controller_id, channel from core_variables where typ = "din"');
        foreach(\App\Http\Models\ControllersModel::get() as $din) {
            try {
                foreach($din_channels as $chan) {
                    $find = false;
                    foreach($vars as $var) {
                        if ($var->controller_id == $din->id && $var->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }
                    if (!$find) {
                        $app_control = 1; // По умолчанию СВЕТ
                        
                        $item = new \App\Http\Models\VariablesModel();
                        $item->controller_id = $din->id;
                        $item->typ = 'din';
                        $item->name = 'temp for din';
                        //$item->comm = Lang::get('admin/hubs.app_control.'.$app_control);
                        $item->ow_id = null;
                        $item->channel = $chan;
                        $item->app_control = $app_control;
                        $item->save(['withoutevents']);
                        $item->name = 'din_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            } catch (\Exception $ex) {
                Log::info($ex);
                return 'ERROR';
            }
        }
        
        // Ceneration of devices for network hubs
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
                        $appControl = $decodeChannel($chan);
                        
                        $item = new \App\Http\Models\VariablesModel();
                        $item->controller_id = $dev->controller_id;
                        $item->typ = 'ow';
                        $item->name = 'temp for ow';
                        //$item->comm = Lang::get('admin/hubs.app_control.'.$appControl);
                        $item->ow_id = $dev->id;
                        $item->channel = $chan;
                        $item->app_control = $appControl;
                        $item->save(['withoutevents']);
                        $item->name = 'ow_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return 'ERROR';
        }
        
        return 'OK';
    }
    
    /**
     * This route builds the firmware and returns a build report view 
     * containing the update controls.
     * 
     * @return type
     */
    public function firmware() 
    {
        $makeError = false;
        $text = '';
        try {
            $firmware = new \App\Library\Firmware();
            
            $firmware->generateConfig();
            
            $outs = [];
            if ($firmware->make($outs)) {
                $text = implode("\n", $outs);
            } else {
                $makeError = true;
                $text = implode("\n", $outs);
            }
        } catch (\Exception $ex) {
            $makeError = true;
            $text = $ex->getMessage();
        }
        
        return view('admin.hubs.firmware', [
            'data' => $text,
            'makeError' => $makeError,
        ]);
    }
    
    /**
     * This route sends the rs485-demon command to start uploading firmware 
     * to the controllers.
     * 
     * @return string
     */
    public function firmwareStart() 
    {
        \App\Http\Models\PropertysModel::setRs485Command('FIRMWARE');
        \App\Http\Models\PropertysModel::setRs485CommandInfo('', true);
        return 'OK';
    }
    
    /**
     * This route to query the firmware status now.
     * 
     * @return type
     */
    public function firmwareStatus() 
    {
        try {
            $info = \App\Http\Models\PropertysModel::getRs485CommandInfo();
            if ($info == 'COMPLETE') {
                return response()->json([                    
                    'firmware' => 'COMPLETE',
                ]);
            } else 
            if (strpos($info, 'ERROR') !== false) {
                return response()->json([
                    'error' => $info,
                ]);
            } else {
                $a = explode(';', $info);                    
                if (count($a) < 2) {
                    $a = ['', 0];
                }
                return response()->json([
                    'controller' => $a[0],
                    'percent' => $a[1],
                ]);                
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
            ]);
        }
    }
    
    /**
     * This route sends the rs485-demon command to reboot all hubs. 
     * 
     * @return string
     */
    public function hubsReset() 
    {
        try {
            \App\Http\Models\PropertysModel::setRs485Command('RESET');
            return 'OK';            
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }
    
    /**
     * This is the rs485-demon reboot method.
     * 
     * @param \App\Http\Controllers\Admin\DemonManager $demonManager
     * @return string
     */
    private function _restartRs485Demon() 
    {
        $demonManager = new \App\Library\DemonManager();
        $demon = 'rs485-demon';
        try {
            \App\Http\Models\PropertysModel::setAsRunningDemon($demon);
            $demonManager->restart($demon);
            return 'OK';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
