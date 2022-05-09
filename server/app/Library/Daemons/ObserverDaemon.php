<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * Description of ObserverDaemon
 *
 * @author soliton
 */
class ObserverDaemon extends BaseDaemon
{   
    /**
     * 
     */
    public function execute() 
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        
        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/observer-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        $this->initialization();
                
        while(1) {
            if (!$this->checkEvents()) break;
            
            usleep(100000);
        }
    }
    
    /**
     * 
     * @param type $device
     */
    protected function deviceChangeValue(&$device)
    {
        
    }
}
