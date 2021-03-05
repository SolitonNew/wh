<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library\Demons;

use \Carbon\Carbon;
use DB;
use Lang;
use Log;

/**
 * Description of CommandDemon
 *
 * @author soliton
 */
class CommandDemon extends BaseDemon {    
    /**
     * 
     */
    public function execute() {        
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::delete('delete from core_execute');
        
        $lastProcessedID = -1;

        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/demons.command-demon-title'));
        $this->printLine(str_repeat('-', 100));
        
        while(1) {
            $sql = "select *
                      from core_execute 
                     where ID > $lastProcessedID
                    order by ID";

            foreach(DB::select($sql) as $row) {
                $this->printLine(Lang::get('admin/demons.command-demon-line', [
                    'datetime' => Carbon::now(),
                    'command' => $row->COMMAND,
                ]));
                
                $execute = new \App\Library\Script\PhpExecute($row->COMMAND);
                $res = $execute->run();
                if ($res) {
                    $this->printLine($res);
                }
                
                $lastProcessedID = $row->ID;
            }
            
            usleep(100000);
        }
    }
}
