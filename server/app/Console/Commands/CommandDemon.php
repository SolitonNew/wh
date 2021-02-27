<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Lang;

class CommandDemon extends Command
{
    use PrintToDB;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выпоняет внутрисистемные комманды';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
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
    
    private $_commands = [
        \App\Console\Commands\DemonCommands\InfoCommand::class,
        \App\Console\Commands\DemonCommands\VariableCommand::class,
        \App\Console\Commands\DemonCommands\PlayCommand::class,
        \App\Console\Commands\DemonCommands\SpeechCommand::class,
    ];
    
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
                    $this->printLine($output);
                    break;
                }
            } catch (\Exception $ex) {
                $this->printLine($ex->getMessage());
            }
        }
    }
}
