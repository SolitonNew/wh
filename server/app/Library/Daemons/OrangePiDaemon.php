<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\Device;
use Illuminate\Support\Facades\Lang;
use DB;

/**
 * Description of OrangePiDaemon
 *
 * @author User
 */
class OrangePiDaemon extends BaseDaemon
{
    use DeviceManagerTrait;
    
    public function execute()
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/orangepi-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        // Init hubs  -------------
        if (!$this->initHubs('orangepi')) return ;
        // ------------------------
        
        $this->_initGPIO();
        
        // Init device changes trait
        $this->initDeviceChanges();
        // -------------------------
        
        $lastMinute = \Carbon\Carbon::now()->startOfMinute();
        
        try {
            while (1) {
                // Get changes of the variables
                $this->checkDeviceChanges();
                // -----------------------------
                
                // Get Orange Pi system info
                $minute = \Carbon\Carbon::now()->startOfMinute();
                if ($minute->gt($lastMinute)) {
                    $this->_getSystemInfo();
                }
                $lastMinute = $minute;
                // -----------------------------
                
                usleep(100000);
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        } finally {
            
        }
    }
    
    /**
     * 
     */
    private function _initGPIO()
    {
        $channels = config('orangepi.channels');
        
        $hubIds = $this->_hubs
            ->pluck('id')
            ->toArray();
        
        $devices = Device::whereIn('hub_id', $hubIds)
            ->whereTyp('orangepi')
            ->get();
        
        foreach ($channels as $chan => $num) {
            if ($num == -1) continue;
            try {
                $res = [];
                
                foreach ($devices as $device) {
                    if ($device->channel == $chan) {
                        if ($device->value) {
                            exec('gpioset 0 '.$num.'=1 2>&1', $res);
                        } else {
                            exec('gpioset 0 '.$num.'=0 2>&1', $res);
                        }
                        break;
                    }
                }
                
                if (count($res)) {
                    throw new \Exception(implode('; ', $res));
                }
                $this->printLine('GPIO ['.$chan.'] ENABLED');
            } catch (\Exception $ex) {
                $this->printLine('GPIO ['.$chan.'] ERROR: '.$ex->getMessage());
            }
        }
        
        $this->printLine(str_repeat('-', 100));
    }
    
    /**
     * 
     * @param type $chan
     * @param type $value
     * @throws \Exception
     */
    private function _setValueGPIO($chan, $value)
    {
        try {
            $channels = config('orangepi.channels');
            $num = $channels[$chan];
            
            if ($num == -1) return ;
            
            $res = [];
            if ($value) {
                exec('gpioset 0 '.$num.'=1 2>&1', $res);
            } else {
                exec('gpioset 0 '.$num.'=0 2>&1', $res);
            }
            if (count($res)) {
                throw new \Exception(implode('; ', $res));
            }
            $this->printLine('['.parse_datetime(now()).'] GPIO ['.$chan.'] SET VALUE: '.($value ? 'ON' : 'OFF'));
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        }
    }
    
    /**
     * 
     * @param type $device
     */
    protected function _deviceChange($device)
    {
        if ($device->typ == 'orangepi') {
            $this->_setValueGPIO($device->channel, $device->value);
        }
    }
    
    /**
     * 
     * @return type
     */
    private function _getSystemInfo()
    {
        try {
            $val = file_get_contents('/sys/devices/virtual/thermal/thermal_zone0/temp');
            $temp = preg_replace("/[^0-9]/", "", $val);

            if ($temp > 200) {
                $temp = round($temp / 1000);
            } else {
                $temp = round($temp);
            }

            foreach ($this->_devices as $dev) {
                if ($dev->typ == 'orangepi' && $dev->channel == 'PROC_TEMP') {
                    if (round($dev->value) != $temp) {
                        Device::setValue($dev->id, $temp);
                    }
                }
            }    
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        }
        
    }
}
