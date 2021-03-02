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

/**
 * Description of CommandDemon
 *
 * @author soliton
 */
class CommandDemon extends BaseDemon {
    
    /**
     *
     * @var type 
     */
    private $_commands = [
        \App\Library\Demons\Commands\Info::class,
        \App\Library\Demons\Commands\Variable::class,
        \App\Library\Demons\Commands\Play::class,
        \App\Library\Demons\Commands\Speech::class,
    ];
    
    /**
     * 
     */
    public function execute() {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::delete('delete from core_execute');
        
        $lastProcessedID = -1;

        $this->printLine('');
        $this->printLine('');
        $this->printLine('');
        $this->printLine(Lang::get('admin/demons.command-demon-title'));
        
        while(1) {
            $sql = "select *
                      from core_execute 
                     where ID > $lastProcessedID
                    order by ID";

            foreach(DB::select($sql) as $row) {
                foreach(explode("\n", $row->COMMAND) as $command) {
                    $this->printLine(Lang::get('admin/demons.command-demon-line', [
                        'datetime' => Carbon::now(),
                        'command' => $command,
                    ]));
                    $this->_execute($command);
                }
                $lastProcessedID = $row->ID;
            }
            
            usleep(100000);
        }
    }
    
    /**
     * 
     * @param string $command
     */
    private function _execute(string $command) {
        foreach($this->_commands as $commandClass) {
            try {
                $c = new $commandClass();
                $output = '';
                if ($c->execute($command, $output)) {
                    if ($output != '') {
                        $this->printLine($output);
                    }
                    break;
                }
            } catch (\Exception $ex) {
                $this->printLine($ex->getMessage());
            }
        }
    }
}
