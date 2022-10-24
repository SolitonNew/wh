<?php

namespace App\Library\Daemons;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\ScriptString;

class CommandDaemon extends BaseDaemon
{
    public const SIGNATURE = 'command-daemon';

    public const PROPERTY_NAME = 'COMMAND';

    /**
     * @return bool
     */
    public static function canRun(): bool
    {
        return true;
    }

    /**
     * The overridden method.
     * 1. Clear command log
     * 2. Start infinity loop
     * 3. Listening to the command log and executing commands.
     *
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        DB::delete('delete from core_execute');

        $lastProcessedID = -1;

        $this->printInitPrompt(Lang::get('admin/daemons/command-daemon.description'));

        while(1) {
            $sql = "select *
                      from core_execute
                     where id > $lastProcessedID
                    order by id";

            foreach (DB::select($sql) as $row) {
                $command = $row->command;
                if (str_starts_with($command, '{')) {
                    $commandStr = $this->executeRawCommand($command);
                } else {
                    $commandStr = $this->executeCommand($command);
                }
                
                $this->printLine(Lang::get('admin/daemons/command-daemon.line', [
                    'datetime' => parse_datetime(now()),
                    'command' => $commandStr,
                ]));
                
                $lastProcessedID = $row->id;
            }

            usleep(100000);
        }
    }
    
    /**
     * @param string $command
     * @return string
     */
    private function executeCommand(string $command): string
    {
        $execute = new \App\Library\Script\PhpExecute($row->command);
        $res = $execute->run();
        if ($res) {
            $this->printLine($res);
        }
        return $command;
    }
    
    /**
     * 
     * @param string $rawCommand
     * @return string
     */
    private function executeRawCommand(string $rawCommand): string
    {
        $pack = json_decode($rawCommand);
        if ($pack) {
            $data = $pack->data;
            $args = '';
            switch ($pack->command) {
                case 'speech':
                    $target = ScriptString::getStringById(array_shift($data));
                    $phrase = ScriptString::getStringById(array_shift($data));
                    $args = "'$target', '$phrase'".(count($data) ? ', ' : '').implode(', ', $data);
                    break;
                case 'play':
                    $target = ScriptString::getStringById(array_shift($data));
                    $media = ScriptString::getStringById(array_shift($data));
                    $args = "'$target', '$media'".(count($data) ? ', ' : '').implode(', ', $data);
                    break;
                case 'print':
                    $text = ScriptString::getStringById(array_shift($data));
                    $args = "'$text'".(count($data) ? ', ' : '').implode(', ', $data);
                    break;
            }
            
            return $this->executeCommand($pack->command.'('.$args.');');
        }
        
        return 'Command Not Found';
    }
}
