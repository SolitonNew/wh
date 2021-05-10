<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use Lang;

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
        
        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/observer-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');        
        
        
        while (1) {
            usleep(200000);
        }
    }
}
