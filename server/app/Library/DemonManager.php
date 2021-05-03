<?php

namespace App\Library;

use Log;

/**
 * DemonManager Process Manager.
 *
 * @author soliton
 */
class DemonManager 
{
    /**
     *     
     * @var type 
     */
    protected $_demons = [];
    
    /**
     * 
     */
    public function __construct() 
    {
        $this->_demons = config('demons.list');
    }
    
    public function demons() 
    {
        return $this->_demons;
    }
    
    /**
     * Checks the correctness of the ID by checking the list of registered.
     * 
     * @param type $id
     * @return type
     */
    public function exists(string $id) 
    {
        return in_array($id, $this->_demons);
    }
    
    /**
     * Checks if a process is running on the system.
     * 
     * @param type $id
     */
    public function isStarted(string $id) 
    {
        if ($this->exists($id)) {
            return count($this->findDemonPID($id)) > 0;
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }
    
    /**
     * Starts a process.
     * 
     * @param type $id
     */
    public function start(string $id) 
    {
        if ($this->exists($id)) {
            exec('php '.base_path().'/artisan '.$id.'>/dev/null &');
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }
    
    /**
     * Stops a process.
     * 
     * @param type $id
     */
    public function stop(string $id) 
    {
        if ($this->exists($id)) {
            foreach($this->findDemonPID($id) as $pid) {
                exec('kill -9 '.$pid);
            }
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }
    
    /**
     * Restarts the process. If the process was stopped, it starts it.
     * 
     * @param type $id
     */
    public function restart(string $id) 
    {
        if ($this->exists($id)) {
            if ($this->isStarted($id)) {
                $this->stop($id);
            }
            $this->start($id);
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }
    
    /**
     * Executes a query to the OS and returns the search result for daemons 
     * in the form of an array.
     * 
     * @param string $id
     * @return type
     */
    public function findDemonPID(string $id) 
    {
        $pids = [];
        exec("ps ax | grep $id | grep -v grep | grep -v 'sh -c '", $outs);
        foreach($outs as $out) {
            $a = explode(' ', trim($out));
            if (count($a)) {
                $pids[] = $a[0];
            }
        }
        return $pids;
    }
    
}
