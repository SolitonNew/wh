<?php

namespace App\Http\Services;

use App\Library\DemonManager;
use App\Http\Models\PropertysModel;

class DemonsService 
{
    private $_demonManager;
    
    public function __construct() 
    {
        $this->_demonManager = new DemonManager();
    }
    
    /**
     * 
     * @return type
     */
    public function demonsList()
    {
        $demons = [];
        foreach($this->_demonManager->demons() as $dem) {
            $stat = $this->_demonManager->isStarted($dem);
            $demons[] = (object)[
                'id' => $dem,
                'stat' => $stat,
            ];
        }
        
        return $demons;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function isStarted($id)
    {
        return $this->_demonManager->isStarted($id);
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function existsDemon($id)
    {
        return $this->_demonManager->exists($id);
    }
    
    /**
     * 
     * @param string $id
     */
    public function demonStart(string $id)
    {        
        try {
            PropertysModel::setAsRunningDemon($id);
            $this->_demonManager->start($id);
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
    public function demonStop(string $id)
    {
        try {
            PropertysModel::setAsStoppedDemon($id);
            $this->_demonManager->stop($id);
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
    public function demonRestart(string $id)
    {
        try {           
            PropertysModel::setAsRunningDemon($id);
            $this->_demonManager->restart($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
