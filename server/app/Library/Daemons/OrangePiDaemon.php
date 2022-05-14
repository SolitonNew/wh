<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\Device;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\I2cHost;
use \Cron\CronExpression;
use App\Models\Property;
use App\Library\OrangePi\I2c\I2c;

/**
 * Description of OrangePiDaemon
 *
 * @author User
 */
class OrangePiDaemon extends BaseDaemon
{
    private $_prevExecuteHostI2cTime = false;
    private $_i2cHosts = [];
    private $_i2cDrivers = [];
    
    public function execute()
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/orangepi-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        if (!$this->initialization('orangepi')) return ;
        
        // Init GPIO pins
        $this->_initGPIO();
        // ------------------------
        
        $lastMinute = \Carbon\Carbon::now()->startOfMinute();
        try {
            while (1) {
                if (!$this->checkEvents()) break;
                
                // Commands processing
                $command = Property::getOrangePiCommand(true);
                switch ($command) {
                    case 'SCAN':
                        $this->_scanNetworks();
                        break;
                }
                
                // I2c hosts
                $this->_processingI2cHosts();
                
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
    protected function initializationHosts()
    {
        $this->_i2cDrivers = config('orangepi.drivers');
        
        $this->_i2cHosts = I2cHost::whereIn('hub_id', $this->_hubIds)
            ->get();
    }
    
    /**
     * 
     */
    private function _initGPIO()
    {
        $channels = config('orangepi.channels');
        
        $enabled = [];
        $errors = [];
        
        foreach ($channels as $chan => $num) {
            if ($num > -1) {
                try {
                    $res = '';
                    foreach ($this->_devices as $device) {
                        if (in_array($device->hub_id, $this->_hubIds) && $device->channel == $chan) {
                            if ($device->value) {
                                $res = shell_exec('gpioset 0 '.$num.'=1 2>&1');
                            } else {
                                $res = shell_exec('gpioset 0 '.$num.'=0 2>&1');
                            }
                            break;
                        }
                    }

                    if ($res) {
                        throw new \Exception($res);
                    }
                    
                    $enabled[] = $chan;
                } catch (\Exception $ex) {
                    $errors[$chan] = $ex->getMessage();
                }
            }
        }
        
        $this->printLine('GPIO ['.implode(', ', $enabled).'] ENABLED');
        if (count($errors)) {
            foreach ($errors as $chan => $error) {
                $this->printLine('GPIO ['.$chan.'] INIT ERROR: '.$error);
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
    protected function deviceChangeValue($device)
    {
        if (in_array($device->hub_id, $this->_hubIds) && $device->typ == 'orangepi') {
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
                    break;
                }
            }    
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        }
    }
    
    /**
     * 
     * @return type
     */
    private function _processingI2cHosts()
    {
        $now = floor(\Carbon\Carbon::now()->timestamp / 60);
        
        // Checking for execute after daemon restart.
        if ($this->_prevExecuteHostI2cTime === false) {
            $this->_prevExecuteHostI2cTime = $now;
            return ;
        }
        
        // Checking for execute at ever minutes.
        if ($now == $this->_prevExecuteHostI2cTime) {
            return ;
        }
        
        // Storing the previous time value
        $this->_prevExecuteHostI2cTime = $now;
        
        $outData = [];
        
        foreach ($this->_i2cHosts as $host) {
            if (!isset($this->_i2cDrivers[$host->typ])) continue;
            
            $cron = $this->_i2cDrivers[$host->typ]['cron'];
            if (CronExpression::factory($cron)->isDue()) {
                $class = $this->_i2cDrivers[$host->typ]['class'];
                try {
                    $driver = new $class($host->address);
                    $result = $driver->getData();
                    
                    if ($result) {
                        foreach ($result as $chan => $val) {
                            foreach ($this->_devices as $dev) {
                                if ($dev->host_id == $host->id && 
                                    $dev->typ == 'i2c' &&
                                    $dev->channel == $chan &&
                                    $dev->value != $val)
                                {
                                    Device::setValue($dev->id, $val);
                                    $outData[] = $dev->id.': '.$val;
                                    break;
                                }
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
        
        if (count($outData)) {
            $s = "[".parse_datetime(now())."] I2C [".implode(", ", $outData)."]";
            $this->printLine($s);
        }
    }
    
    /**
     * 
     */
    private function _scanNetworks()
    {
        Property::setOrangePiCommandInfo('', true);
        
        $addresses = I2c::scan();
        
        $new = 0;
        $lost = 0;
        
        $oldHosts = $this->_i2cHosts;
        
        // Finding a lost entries
        foreach ($oldHosts as $oldHost) {
            if (!in_array($oldHost->address, $addresses)) {
                $lost++;
                $oldHost->lost = 1;
            } else {
                $oldHost->lost = 0;
            }
            $oldHost->save();
        }
        
        // Check found entries.
        foreach ($addresses as $addr) {
            $find = false;
            foreach ($oldHost as $oldHost) {
                if ($addr == $oldHost->address) {
                    $find = true;
                    break;
                }
            }
            
            if (!$find) {
                $new++;
                // Add to the list immediately.
                // ...
            }
        }
        
        $report = [];
        $s = "I2C SEARCH. [TOTAL: ".count($addresses).", NEW: ".$new.", LOST: ".$lost."] ";
        $this->printLine($s);
        $report[] = $s;
        $report[] = str_repeat('-', 35);
        foreach ($addresses as &$addr) {
            $report[] = sprintf("x%'02X", $addr);
        }
        $report[] = str_repeat('-', 35);
        $report[] = '';
        
        Property::setOrangePiCommandInfo(implode("\n", $report));
        Property::setOrangePiCommandInfo('END_SCAN');
    }
}
