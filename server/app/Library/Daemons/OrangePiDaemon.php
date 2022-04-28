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
                $this->printLine('GPIO ['.$chan.'] ENABLED');
            } catch (\Exception $ex) {
                $this->printLine('GPIO ['.$chan.'] ERROR: '.$ex->getMessage());
            }
        }
        
        $this->printLine(str_repeat('-', 100));
    }
}
