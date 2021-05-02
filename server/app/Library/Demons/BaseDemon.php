<?php

namespace App\Library\Demons;

use Log;

/**
 * This is the base class for all daemons.
 *
 * @author soliton
 */
class BaseDemon {
    
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
            $item = new \App\Http\Models\WebLogsModel();
            $item->demon = $this->_signature;
            $item->data = $text;
            $item->save();
            
            echo "$text\n";
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
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
