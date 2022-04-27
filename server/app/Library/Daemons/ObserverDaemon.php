<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\DeviceChangeMem;
use DB;
use Lang;
use Log;

/**
 * Description of ObserverDaemon
 *
 * @author soliton
 */
class ObserverDaemon extends BaseDaemon
{   
    private $_lastDeviceChangesID = -1;
    
    /**
     * 
     */
    public function execute() 
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/observer-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');        
        
        $this->_lastChangeID = DeviceChangeMem::max('id') ?? -1;
        
        /*sleep(1);
        $this->printLine('ZZZZZZZZZ');
        $this->printLine('ZZZZZZZZZ');
        sleep(1);
        for ($i = 0; $i <= 100; $i++) {
            $this->printProgress($i);
            usleep(50000);
        }
        $this->printLine('ZZZZZZZZZ'); */
        
        while(1) {
            $changes = DeviceChangeMem::with('device')
                ->where('id', '>', $this->_lastDeviceChangesID)
                ->orderBy('id', 'asc')
                ->get();
            foreach($changes as $item) {
                $this->_processedDevice($item);
                $this->_lastDeviceChangesID = $item->id;
            }
            usleep(200000);
        }
    }
    
    /**
     * 
     * @param type $changes
     */
    private function _processedDevice(&$item)
    {
        $device = $item->device;
        
        if (!$device) return ;
        
        switch ($device->app_control) {
            case 1: // Light
                //
                break;
        }
    }
}
