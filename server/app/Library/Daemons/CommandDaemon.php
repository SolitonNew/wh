<?php

namespace App\Library\Daemons;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class CommandDaemon extends BaseDaemon
{
    public const SIGNATURE = 'commands-daemon';

    public const PROPERTY_NAME = 'COMMAND';

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
