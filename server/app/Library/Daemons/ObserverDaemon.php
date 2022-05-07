<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\DeviceChangeMem;
use App\Models\Device;
use DB;
use Illuminate\Support\Facades\Lang;

/**
 * Description of ObserverDaemon
 *
 * @author soliton
 */
class ObserverDaemon extends BaseDaemon
{   
    /**
     *
     * @var type 
     */
    private $_lastSyncDeviceChangesID = -1;
    
    /**
     *
     * @var type 
     */
    private $_devices = [];
    
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
        
        // Get last id of the change log
        $this->_lastSyncDeviceChangesID = DeviceChangeMem::max('id') ?? -1;
        
        // Get an up-to-date list of all variables
        $this->_devices = Device::orderBy('id')
            ->get();
        
        while(1) {
            $changes = DeviceChangeMem::where('id', '>', $this->_lastSyncDeviceChangesID)
                ->orderBy('id', 'asc')
                ->get();
            if (count($changes)) {
                $this->_lastSyncDeviceChangesID = $changes[count($changes) - 1]->id;
                
                $this->_syncVariables($changes);
            }
            
            usleep(100000);
        }
    }
    
    /**
     * 
     * @param type $changes
     */
    private function _syncVariables(&$changes)
    {
        foreach ($changes as $change) {
            foreach ($this->_devices as $device) {
                if ($device->id == $change->device_id) {
                    if ($device->value != $change->value) {
                        //
                    }
                    break;
                }
            }
        }
    }
}
