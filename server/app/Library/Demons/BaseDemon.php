<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use Log;

/**
 * Description of DemobBase
 *
 * @author soliton
 */
class BaseDemon {
    
    /**
     *
     * @var type 
     */
    protected $_signature = '';
    
    public function __construct($signature) {
        $this->_signature = $signature;
    }

    /**
     * 
     * @param type $text
     */
    public function printLine($text) {
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
     * 
     */
    public function execute() {
        
    }
    
}
