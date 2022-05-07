<?php

namespace App\Library\Daemons;

use App\Models\Device;
use App\Models\DeviceChangeMem;
use App\Library\Script\PhpExecute;
use Illuminate\Support\Facades\DB;

trait DeviceManagerTrait 
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
    protected function initDeviceChanges()
    {
        // Get last id of the change log
        $this->_lastSyncDeviceChangesID = DeviceChangeMem::max('id') ?? -1;
        
        // Get an up-to-date list of all variables
        $this->_devices = Device::orderBy('id')
            ->get();
    }
    
    /**
     * Get changes of the variables
     */
    protected function checkDeviceChanges()
    {
        $counter = 0;
        do {        
            $repeat = false;
            
            $changes = DeviceChangeMem::where('id', '>', $this->_lastSyncDeviceChangesID)
                    ->orderBy('id', 'asc')
                    ->get();
            if (count($changes)) {
                $this->_lastSyncDeviceChangesID = $changes[count($changes) - 1]->id;

                if ($this->_syncVariables($changes)) {
                    $repeat = true;
                    usleep(5000);
                }
            }
            
            if ($counter++ > 10) break;
        } while ($repeat);
    }
    
    /**
     * 
     * @param type $changes
     * @return boolean
     */
    private function _syncVariables(&$changes)
    {
        $scriptExecuted = false;
        foreach ($changes as $change) {
            foreach ($this->_devices as $device) {
                if ($device->id == $change->device_id) {
                    if ($device->value != $change->value) {
                        // Store new device value
                        $device->value = $change->value;

                        // Call change value handler
                        $this->_deviceChangeValue($device);

                        // Run event script if it's attached
                        if ($this->_executeEvents($device)) {
                            $scriptExecuted = true;
                        }
                    }
                    break;
                }
            }
        }
        
        return $scriptExecuted;
    }
    
    /**
     * 
     * @param type $device
     */
    protected function _deviceChangeValue($device)
    {
        //
    }
    
    /**
     * 
     * @param type $device
     */
    private function _executeEvents(&$device)
    {
        $hubIds = $this->_hubs
            ->pluck('id')
            ->toArray();
        
        if (!in_array($device->hub_id, $hubIds)) return false;
        
        $sql = "select s.comm, s.data
                  from core_device_events de, core_scripts s
                 where de.device_id = ".$device->id."
                   and de.script_id = s.id";
        
        $scriptExecuted = false;
        
        foreach (DB::select($sql) as $script) {
            try {
                $execute = new PhpExecute($script->data);
                $execute->run();
                $s = "[".parse_datetime(now())."] RUN SCRIPT '".$script->comm."' \n";
                $this->printLine($s); 
                
                $scriptExecuted = true;
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
        
        return $scriptExecuted;
    }
}
