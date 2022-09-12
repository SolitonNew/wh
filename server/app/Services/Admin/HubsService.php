<?php

namespace App\Services\Admin;

use App\Models\Hub;
use App\Models\Device;
use App\Models\OwHost;
use App\Models\I2cHost;
use App\Models\ExtApiHost;
use App\Models\CamcorderHost;
use App\Models\Property;
use App\Library\DaemonManager;
use App\Library\Firmware\Din;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HubsService 
{
    /**
     * 
     * @return type
     */
    public function dinHubsScan()
    {
        Property::setDinCommand('OW SEARCH');
        usleep(500000);
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
    
    /**
     * 
     * @return type
     */
    public function orangepiHubScan()
    {
        Property::setOrangePiCommand('SCAN');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = Property::getOrangePiCommandInfo();
            if ($t = strpos($text, 'END_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }        
        return $text;
    }
    
    /**
     * 
     * @param int $hubID
     */
    public function generateDevsByHub(int $hubID)
    {
        $hub = Hub::find($hubID);
        
        switch ($hub->typ) {
            case 'din':
                $this->_generateDinDevsByHub($hubID);
                break;
            case 'orangepi':
                $this->_generateOrangePiDevsByHub($hubID);
                break;
            case 'camcorder':
                $this->_generateCamcorderDevsByHub($hubID);
                break;
            case 'extapi':
                $this->_generateExtApiDevsByHub($hubID);
                break;
        }
    }
    
    /**
     * 
     * @param type $channel
     * @param type $default
     * @return int
     */
    private function _decodeChannelTyp($channel, $default = 0)
    {
        $channelControl = [
            1 => ['R1', 'R2', 'R3', 'R4'],      // Light
            2 => ['LEFT', 'RIGHT', 'LEFT_LONG', 'RIGHT_LONG'], // Switch
            //3 => [],                          // Socket
            4 => ['T', 'TEMP', 'PROC_TEMP'],    // Termometr
            //5 => [],                          // Termostat
            6 => ['REC'],                       // Camcorder
            7 => ['F1', 'F2', 'F3', 'F4'],      // Venting
            8 => ['P1', 'P2', 'P3', 'P4', 'MOTION'],  // Motion sensor
            //9 => [],                          // Leakage sensor
            10 => ['H'],                        // Humidity
            11 => ['CO'],                       // Gas sensor
            //12 => [],                         // Door sensor
            13 => ['P'],                        // Atm. pressure
            14 => ['AMP'],                      // Currency sensor
            15 => ['G', 'WS'],                  // Speed
            16 => ['WD'],                       // Direction
            17 => ['V', ''],                    // Distance
            18 => ['H', 'CC'],                  // Percents
            19 => ['MP'],                       // Height
        ];
        
        foreach($channelControl as $key => $val) {
            if (in_array($channel, $val)) {
                return $key;
            }
        }
        
        return $default;
    }
    
    /**
     * 
     * @param int $hubID
     * @return type
     */
    private function _generateDinDevsByHub(int $hubID)
    {
        $din_channels = config('din.'.Property::getDinSettings()->mmcu.'.channels');
        $devs = DB::select('select hub_id, channel from core_devices where hub_id = '.$hubID.' and typ = "din"');

        try {
            foreach($din_channels as $chan) {
                $find = false;
                foreach($devs as $dev) {
                    if ($dev->hub_id == $hubID && $dev->channel == $chan) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $item = new Device();
                    $item->hub_id = $hubID;
                    $item->typ = 'din';
                    $item->name = 'temp for din';
                    $item->host_id = null;
                    $item->channel = $chan;
                    $item->app_control = $this->_decodeChannelTyp($chan, 1);
                    $item->save(['withoutevents']);
                    $item->name = 'din_'.$item->id.'_'.$chan;
                    $item->save();
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
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
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'ow';
                        $item->name = 'temp for ow';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->_decodeChannelTyp($chan);
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
     * 
     * @param int $hubID
     * @return type
     */
    private function _generateOrangePiDevsByHub(int $hubID)
    {
        $channels = config('orangepi.channels');
        $devs = DB::select('select hub_id, channel from core_devices where hub_id = '.$hubID.' and typ = "orangepi"');

        try {
            foreach($channels as $chan => $num) {
                $find = false;
                foreach($devs as $dev) {
                    if ($dev->hub_id == $hubID && $dev->channel == $chan) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $item = new Device();
                    $item->hub_id = $hubID;
                    $item->typ = 'orangepi';
                    $item->name = 'temp for din';
                    $item->host_id = null;
                    $item->channel = $chan;
                    $item->app_control = $this->_decodeChannelTyp($chan, 1);
                    $item->save(['withoutevents']);
                    $item->name = 'orangepi_'.$item->id.'_'.$chan;
                    $item->save();
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
        
        // Generation of devices for network hubs
        // Generation of devices for network hubs
        $hosts = I2cHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('i2c')->get();
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
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'i2c';
                        $item->name = 'temp for i2c';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->_decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'i2c_'.$item->id.'_'.$chan;
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
     * 
     * @param type $hubID
     * @return type
     */
    private function _generateExtApiDevsByHub($hubID)
    {
        $hosts = ExtApiHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('extapi')->get();
        
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
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'extapi';
                        $item->name = 'temp for extapi';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->_decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'extapi_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
    }
    
    private function _generateCamcorderDevsByHub($hubID)
    {
        $hosts = CamcorderHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('camcorder')->get();
        
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
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'camcorder';
                        $item->name = 'temp for camcorder';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->_decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'cam_'.$item->id.'_'.$chan;
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
    public function generateDevs() 
    {
        foreach (Hub::get() as $hub) {
            $this->generateDevsByHub($hub->id);
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
            'extapi-daemon',
            'orangepi-daemon',
            'camcorder-daemon',
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
            $firmware = new Din();
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
