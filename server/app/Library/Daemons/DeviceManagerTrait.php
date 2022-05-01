<?php

namespace App\Library\Daemons;

use App\Models\Device;
use App\Models\DeviceChangeMem;
use App\Library\Script\PhpExecute;
use DB;

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
        $repeat = true;
        $counter = 0;
        while ($repeat) {
            $repeat = false;
        
            $changes = DeviceChangeMem::where('id', '>', $this->_lastSyncDeviceChangesID)
                    ->orderBy('id', 'asc')
                    ->get();
            if (count($changes)) {
                $this->_lastSyncDeviceChangesID = $changes[count($changes) - 1]->id;

                if ($this->_syncVariables($changes)) {
                    $repeat = true;
                }
            }
            
            if ($counter++ > 5) break;
            
            if ($repeat) {
                usleep(10000);
            }
        }
    }
    
    /**
     * 
     * @param type $changes
     */
    private function _syncVariables(&$changes)
    {
        $executed = false;
        foreach ($changes as $change) {
            foreach ($this->_devices as $device) {
                if ($device->id == $change->device_id) {
                    if ($device->value != $change->value) {
                        // Store new device value
                        $device->value = $change->value;

                        $this->_deviceChange($device);

                        // Run event script if it's attached
                        if ($this->_executeEvents($device)) {
                            $executed = true;
                        }
                    }
                    break;
                }
            }
        }
    }
    
    /**
     * 
     * @param type $device
     */
    protected function _deviceChange($device)
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
        
        $executed = false;
        
        foreach (DB::select($sql) as $script) {
            try {
                $execute = new PhpExecute($script->data);
                $execute->run();
                $s = "[".parse_datetime(now())."] RUN SCRIPT '".$script->comm."' \n";
                $this->printLine($s); 
                
                $executed = true;
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
        
        return $executed;
    }
}
