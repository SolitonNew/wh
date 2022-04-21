<?php

namespace App\Library\Daemons;

use \Carbon\Carbon;
use DB;
use Lang;

/**
 * Description of CommandDaemon
 *
 * @author soliton
 */
class CommandDaemon extends BaseDaemon 
{    
    /**
     * The overridden method.
     * 1. Clear command log
     * 2. Start infinity loop
     * 3. Listening to the command log and executing commands.
     */
    public function execute() 
    {        
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::delete('delete from core_execute');
        
        $lastProcessedID = -1;

        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        $this->printLine(Lang::get('admin/daemons/command-daemon.description'));
        $this->printLine(str_repeat('-', 100));
        $this->printLine('');
        
        while(1) {
            $sql = "select *
                      from core_execute 
                     where id > $lastProcessedID
                    order by id";

            foreach(DB::select($sql) as $row) {
                $this->printLine(Lang::get('admin/daemons/command-daemon.line', [
                    'datetime' => parse_datetime(now()),
                    'command' => $row->command,
                ]));
                
                $execute = new \App\Library\Script\PhpExecute($row->command);
                $res = $execute->run();
                if ($res) {
                    $this->printLine($res);
                }
                
                $lastProcessedID = $row->id;
            }
            
            usleep(100000);
        }
    }
}
