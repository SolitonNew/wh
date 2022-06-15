<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Models\CamcorderHost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * Description of CamcorderDaemon
 *
 * @author soliton
 */
class CamcorderDaemon extends BaseDaemon
{
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
        $this->printLine(Lang::get('admin/daemons/camcorder-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        // Base init
        if (!$this->initialization('camcorder')) return ;
        
        try {
            while (1) {
                // ExtApi Host Providers Execute
                $this->_executeHostProviders();
                
                // Check event log
                if (!$this->checkEvents()) break;
                
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
        $ids = $this->_hubs
            ->pluck('id')
            ->toArray();
        
        $hosts = CamcorderHost::whereIn('hub_id', $ids)
            ->get();
        
        $list = [];
        foreach ($hosts as $host) {
            $driver = $host->driver();
            $this->_providers[$host->id] = $driver;
            $list[] = $host->name.' ('.$driver->title.')';
        }
        
        $this->printLine('CAMCORDERS USED: ['.implode(', ', $list).']');
        
        // Camcorder folder
        $folder = base_path('storage/app/camcorder');
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        
        // Thumbnails folder
        $folder = base_path('storage/app/camcorder/thumbnails');
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        
        // Videos folder
        $folder = base_path('storage/app/camcorder/videos');
        if (!file_exists($folder)) {
            mkdir($folder);
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
                // Request Thumbnail
                if ($provider->canThumbnailRequest()) {
                    $result = $provider->requestThumbnail();
                    $s = "[".parse_datetime(now())."] PROVIDER '".$provider->caption." (".$provider->title.")' HAS BEEN REQUEST THUMBNAIL \n";
                    $this->printLine($s); 
                    if ($result) {
                        $this->printLine($result);
                    }
                }
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR FOR '".$provider->title."'\n";
                $s .= $ex->getMessage();
                $this->printLine($s); 
            }
        }
    }
    
    protected function deviceChangeValue($device)
    {
        
    }
}
