<?php

namespace App\Library\Daemons;

use App\Models\WebLogMem;
use App\Models\Property;

/**
 * This is the base class for all daemons.
 *
 * @author soliton
 */
class BaseDaemon {
    
    /**
     * Signature (id) of the daemon
     * @var type 
     */
    protected $_signature = '';
    
    public function __construct($signature) 
    {
        $this->_signature = $signature;
    }

    /**
     * This method of adding a log entry into DB.
     * 
     * @param type $text
     */
    public function printLine($text) 
    {
        try {
            $item = new WebLogMem();
            $item->daemon = $this->_signature;
            $item->data = $text;
            $item->save();
            
            echo "$text\n";
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
    }
    
    /**
     * Disabling autorun of this daemon
     */
    public function disableAutorun()
    {
        Property::setAsStoppedDaemon($this->_signature);
    }
    
    /**
     * The launch of this method is automated.
     * Each inheritor of this class must override it and place it inside 
     * the code that the daemon should execute.
     */
    public function execute() 
    {
        
    }
    
}
