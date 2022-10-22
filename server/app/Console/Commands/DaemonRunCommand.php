<?php

namespace App\Console\Commands;

use App\Library\Commands\DaemonRun;
use \Illuminate\Console\Command;

class DaemonRunCommand extends Command
{
    protected $signature = 'daemon:run {daemonId}';
    protected $description = 'daemon:run';

    public function handle(DaemonRun $command)
    {
        $command->execute($this->argument('daemonId'));
    }
}
