<?php

namespace App\Http\Services\Admin;

use App\Library\DaemonManager;
use App\Models\Property;

class DaemonsService 
{
    private $_daemonManager;
    
    public function __construct() 
    {
        $this->_daemonManager = new DaemonManager();
    }
    
    /**
     * 
     * @return type
     */
    public function daemonsList()
    {
        $daemons = [];
        foreach($this->_daemonManager->daemons() as $dem) {
            $stat = $this->_daemonManager->isStarted($dem);
            $daemons[] = (object)[
                'id' => $dem,
                'stat' => $stat,
            ];
        }
        
        return $daemons;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function isStarted($id)
    {
        return $this->_daemonManager->isStarted($id);
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function existsDaemon($id)
    {
        return $this->_daemonManager->exists($id);
    }
    
    /**
     * 
     * @param string $id
     */
    public function daemonStart(string $id)
    {        
        try {
            Property::setAsRunningDaemon($id);
            $this->_daemonManager->start($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param string $id
     */
    public function daemonStop(string $id)
    {
        try {
            Property::setAsStoppedDaemon($id);
            $this->_daemonManager->stop($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
    
    /**
     * 
     * @param string $id
     */
    public function daemonRestart(string $id)
    {
        try {           
            Property::setAsRunningDaemon($id);
            $this->_daemonManager->restart($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
