<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\Hub;
use App\Models\DeviceChangeMem;
use App\Models\Device;
use App\Library\Script\PhpExecute;
use App\Library\SoftHosts\SoftHostsManager;
use App\Models\SoftHost;
use DB;
use Lang;

/**
 * Description of SoftwareDaemon
 *
 * @author soliton
 */
class SoftwareDaemon extends BaseDaemon
{
    /**
     *
     * @var type 
     */
    private $_controllers;
    private $_providers = [];
    private $_prevExecuteHostProviderTime = false;
    
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
        $this->printLine(Lang::get('admin/daemons/software-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        $this->_controllers = Hub::where('id', '>', 0)
                                ->whereTyp('software')
                                ->orderBy('rom', 'asc')
                                ->get();
        
        $this->_initHostProviders();
        
        if (count($this->_controllers) == 0) {
            $this->disableAutorun();
            return;
        }
        
        // Get last id of the change log
        $this->_lastSyncDeviceChangesID = DeviceChangeMem::max('id') ?? -1;
        
        // Get an up-to-date list of all variables
        $this->_devices = Device::orderBy('id')
            ->get();
        
        try {
            while (1) {
                // Software Host Providers Execute
                $this->_executeHostProviders();
                
                // Get changes of the variables
                $changes = DeviceChangeMem::where('id', '>', $this->_lastSyncDeviceChangesID)
                                        ->orderBy('id', 'asc')
                                        ->get();
                if (count($changes)) {
                    $this->_lastSyncDeviceChangesID = $changes[count($changes) - 1]->id;
                }
                $this->_syncVariables($changes);
                // -----------------------------
                usleep(100000);
            }
        } catch (\Exception $ex) {
            $s = "[".now()."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s); 
        } finally {
            
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
    private function _executeEvents(&$device)
    {
        $hubIds = $this->_controllers
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
                $s = "[".now()."] RUN SCRIPT '".$script->comm."' \n";
                $this->printLine($s); 
            } catch (\Exception $ex) {
                $s = "[".now()."] ERROR\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
    }
    
    /**
     * 
     */
    private function _initHostProviders()
    {
        $manager = new SoftHostsManager();
        
        $ids = $this->_controllers
            ->pluck('id')
            ->toArray();
        
        $hosts = SoftHost::whereIn('hub_id', $ids)
            ->get();
        
        foreach ($hosts as $host) {
             $provider = $manager->providerByName($host->typ);
             if ($provider) {
                $provider->assignKey($host->id);
                $provider->assignData($host->data);
                $this->_providers[$host->id] = $provider;
             }
        }
    }
    
    /**
     * 
     * @return type
     */
    private function _executeHostProviders()
    {
        $now = floor(\Carbon\Carbon::now()->timestamp / 60);
        
        // Checking for execute after daemon restart.
        if ($this->_prevExecuteHostProviderTime === false) {
            $this->_prevExecuteHostProviderTime = $now;
            return ;
        }
        
        // Checking for execute at ever minutes.
        if ($now == $this->_prevExecuteHostProviderTime) {
            return ;
        }
        
        // Storing the previous time value
        $this->_prevExecuteHostProviderTime = $now;
        
        foreach ($this->_providers as $id => $provider) {
            try {
                // Request
                if ($provider->canRequest()) {
                    $result = $provider->request();
                    $s = "[".now()."] PROVIDER '".$provider->title."' HAS BEEN REQUESTED \n";
                    $this->printLine($s); 
                    if ($result) {
                        $this->printLine($result);
                    }
                }
                
                // Update
                if ($provider->canUpdate()) {
                    $result = $provider->update();
                    if ($result) {
                        $s = "[".now()."] PROVIDER '".$provider->title."' HAS BEEN UPDATED \n";
                        $this->printLine($s);
                        $this->printLine($result);
                    }
                }
            } catch (\Exception $ex) {
                $s = "[".now()."] ERROR FOR '".$provider->title."'\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
    }
}
