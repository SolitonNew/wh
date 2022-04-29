<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\Device;
use Lang;
use DB;
use Log;

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
        
        try {
            while (1) {
                // Get changes of the variables
                $this->checkDeviceChanges();
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
}
