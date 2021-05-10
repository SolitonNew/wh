<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Daemons;

use App\Http\Models\ControllersModel;
use DB;
use Lang;

/**
 * Description of SoftwareDaemon
 *
 * @author soliton
 */
class SoftwareDaemon extends BaseDaemon
{
    private $_controllers;
    
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
        
        $this->_controllers = ControllersModel::where('id', '>', 0)
                                ->whereTyp('software')
                                ->orderBy('rom', 'asc')
                                ->get();
        
        if (count($this->_controllers) == 0) {
            $this->disableAutorun();
            return;
        }
        
        while (1) {
            usleep(200000);
        }
    }
}
