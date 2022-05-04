<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\SoftHost;
use DB;
use Illuminate\Support\Facades\Lang;

/**
 * Description of SoftwareDaemon
 *
 * @author soliton
 */
class SoftwareDaemon extends BaseDaemon
{
    use DeviceManagerTrait;
    
    /**
     *
     * @var type 
     */
    private $_providers = [];
    private $_prevExecuteHostProviderTime = false;
    
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
        
        // Init hubs  -------------
        if (!$this->initHubs('software')) return ;
        // ------------------------
        
        $this->_initHostProviders();
        
        $a = [];
        foreach ($this->_providers as $provider) {
            $a[] = $provider->title;
        }
        $this->printLine('PROVIDERS USED: ['.implode(', ', $a).']');
        
        // Init device changes trait
        $this->initDeviceChanges();
        // -------------------------
        
        try {
            while (1) {
                // Software Host Providers Execute
                $this->_executeHostProviders();
                
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
    private function _initHostProviders()
    {
        $ids = $this->_hubs
            ->pluck('id')
            ->toArray();
        
        $hosts = SoftHost::whereIn('hub_id', $ids)
            ->get();
        
        foreach ($hosts as $host) {
            $this->_providers[$host->id] = $host->driver();
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
                    $s = "[".parse_datetime(now())."] PROVIDER '".$provider->title."' HAS BEEN REQUESTED \n";
                    $this->printLine($s); 
                    if ($result) {
                        $this->printLine($result);
                    }
                }
                
                // Update
                if ($provider->canUpdate()) {
                    $result = $provider->update();
                    if ($result) {
                        $s = "[".parse_datetime(now())."] PROVIDER '".$provider->title."' HAS BEEN UPDATED \n";
                        if ($result) {
                            $s .= $result;
                        }
                        $this->printLine($s);
                    }
                }
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR FOR '".$provider->title."'\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
    }
}
