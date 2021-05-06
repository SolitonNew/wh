<?php

namespace App\Http\Services;

use App\Http\Models\ControllersModel;
use App\Http\Models\VariablesModel;
use App\Http\Models\PropertysModel;
use App\Library\DemonManager;
use App\Library\Firmware;
use DB;
use Log;

class HubsService 
{
    /**
     * 
     */
    public function hubsScan()
    {
        PropertysModel::setRs485Command('OW SEARCH');
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = PropertysModel::getRs485CommandInfo();
            if ($t = strpos($text, 'END_OW_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }

        // Starting devices generator
        // If the device is not found, it will create
        $this->_generateDevs();
        // --------------------------------------
        
        return $text;
    }
    
    /**
     * This method creted devices entries on each channel if the channel 
     * does not exists.
     * 
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
        foreach(ControllersModel::whereTyp('din')->get() as $din) {
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
                        
                        $item = new VariablesModel();
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
                return ;
            }
        }
        
        // Generation of devices for network hubs
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
                        
                        $item = new VariablesModel();
                        $item->controller_id = $dev->controller_id;
                        $item->typ = 'ow';
                        $item->name = 'temp for ow';
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
            return ;
        }
    }
    
    /**
     * This is the rs485-demon reboot method.
     * 
     * @param \App\Http\Controllers\Admin\DemonManager $demonManager
     * @return string
     */
    public function restartRs485Demon() 
    {
        $demonManager = new DemonManager();
        $demon = 'rs485-demon';
        try {
            PropertysModel::setAsRunningDemon($demon);
            $demonManager->restart($demon);
            return 'OK';
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     */
    public function firmware()
    {
        $makeError = false;
        $text = '';
        try {
            $firmware = new Firmware();
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
        
        return [
            $text, 
            $makeError
        ];
    }
    
    /**
     * 
     */
    public function firmwareStart()
    {
        PropertysModel::setRs485Command('FIRMWARE');
        PropertysModel::setRs485CommandInfo('', true);
    }
    
    /**
     * 
     * @return type
     */
    public function firmwareStatus()
    {
        $demonManager = new DemonManager();
        
        try {
            if (!$demonManager->isStarted('rs485-demon')) {
                return response()->json([                    
                    'firmware' => 'NOTPOSSIBLE',
                ]);
            }
            
            $info = PropertysModel::getRs485CommandInfo();
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
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @return string
     */
    public function hubsReset()
    {
        try {
            PropertysModel::setRs485Command('RESET');           
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
