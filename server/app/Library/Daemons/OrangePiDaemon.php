<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use Lang;
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
     * @throws \Exception
     */
    private function _initGPIO()
    {
        $channels = config('orangepi.channels');
        
        foreach ($channels as $chan => $num) {
            try {
                $res = exec('echo '.$num.' > /sys/class/gpio/export');
                if ($res) {
                    throw new \Exception($res);
                }
                
                $res = exec('out > /sys/class/gpio/gpio'.$num.'/direction');
                if ($res) {
                    throw new \Exception($res);
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
            
            if ($value) {
                $res = exec('1 > /sys/class/gpio/gpio'.$num.'/value');
            } else {
                $res = exec('0 > /sys/class/gpio/gpio'.$num.'/value');
            }
            if ($res) {
                throw new \Exception($res);
            }
            $this->printLine('['.parse_datetime(now()).'] GPIO ['.$chan.'] SET VALUE: '.$value);
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
