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
        $changes = DeviceChangeMem::where('id', '>', $this->_lastSyncDeviceChangesID)
                ->orderBy('id', 'asc')
                ->get();
        if (count($changes)) {
            $this->_lastSyncDeviceChangesID = $changes[count($changes) - 1]->id;
            
            $this->_syncVariables($changes);
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
                        // Store new device value
                        $device->value = $change->value;
                        
                        $this->_deviceChange($device);
                        
                        // Run event script if it's attached
                        $this->_executeEvents($device);
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
        
        if (!in_array($device->hub_id, $hubIds)) return ;
        
        $sql = "select s.comm, s.data
                  from core_device_events de, core_scripts s
                 where de.device_id = ".$device->id."
                   and de.script_id = s.id";
        
        foreach (DB::select($sql) as $script) {
            try {
                $execute = new PhpExecute($script->data);
                $execute->run();
                $s = "[".parse_datetime(now())."] RUN SCRIPT '".$script->comm."' \n";
                $this->printLine($s); 
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
    }
}
