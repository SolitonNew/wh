<?php

namespace App\Services\Admin;

use App\Models\Hub;
use App\Models\Device;
use App\Models\OwHost;
use App\Models\SoftHost;
use App\Models\Property;
use App\Library\DaemonManager;
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
        Property::setDinCommand('OW SEARCH');
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = Property::getDinCommandInfo();
            if ($t = strpos($text, 'END_OW_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }
        
        return $text;
    }
    
    
    public function _generateDevsByHub(int $hubID)
    {
        $hub = Hub::find($hubID);
        
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
            13 => ['P'],                       // Atm. pressure
            14 => ['AMP'],                    // Currency sensor
            15 => ['G', 'WS'],                // Speed
            16 => ['WD'],                     // Direction
            17 => ['V', ''],                  // Distance
            18 => ['H', 'CC'],                // Percents
        ];     
        
        $decodeChannel = function ($channel) use ($channelControl) {
            foreach($channelControl as $key => $val) {
                if (in_array($channel, $val)) {
                    return $key;
                }
            }
            return -1;
        };
        
        if ($hub->typ == 'din') {
            // Generation of devices by channel
            $din_channels = config('firmware.channels.'.config('firmware.mmcu'));
            $devs = DB::select('select hub_id, channel from core_devices where hub_id = '.$hubID.' and typ = "din"');
            
            try {
                foreach($din_channels as $chan) {
                    $find = false;
                    foreach($devs as $dev) {
                        if ($dev->hub_id == $hub->id && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }
                    if (!$find) {
                        $app_control = 1; // По умолчанию СВЕТ

                        $item = new Device();
                        $item->hub_id = $hub->id;
                        $item->typ = 'din';
                        $item->name = 'temp for din';
                        //$item->comm = Lang::get('admin/hubs.app_control.'.$app_control);
                        $item->host_id = null;
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
        $hosts = OwHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('ow')->get();
        
        try {
            foreach($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $appControl = $decodeChannel($chan);
                        
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'ow';
                        $item->name = 'temp for ow';
                        $item->host_id = $host->id;
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
        
        // Generation of devices for software hosts
        $hosts = SoftHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('software')->get();
        
        try {
            foreach($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $appControl = $decodeChannel($chan);
                        
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'software';
                        $item->name = 'temp for software';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $appControl;
                        $item->save(['withoutevents']);
                        $item->name = 'soft_'.$item->id.'_'.$chan;
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
     * This method creted devices entries on each channel if the channel 
     * does not exists.
     * 
     */
    public function _generateDevs() 
    {
        foreach (Hub::get() as $hub) {
            $this->_generateDevsByHub($hub->id);
        }
    }
    
    /**
     * This is the service daemons reboot method.
     * 
     * @return string
     */
    public function restartServiceDaemons() 
    {
        $daemons = [
            'din-daemon', 
            'software-daemon',
        ];
        
        $daemonManager = new DaemonManager();
        try {
            foreach($daemons as $daemon) {
                Property::setAsRunningDaemon($daemon);
                $daemonManager->restart($daemon);
            }
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
        Property::setDinCommand('FIRMWARE');
        Property::setDinCommandInfo('', true);
    }
    
    /**
     * 
     * @return type
     */
    public function firmwareStatus()
    {
        $daemonManager = new DaemonManager();
        
        try {
            if (!$daemonManager->isStarted('din-daemon')) {
                return response()->json([                    
                    'firmware' => 'NOTPOSSIBLE',
                ]);
            }
            
            $info = Property::getDinCommandInfo();
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
            Property::setDinCommand('RESET');           
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
