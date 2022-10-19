<?php

namespace App\Console\Commands;

use \Illuminate\Console\Command;

class DaemonRun extends Command
{
    protected $signature = 'daemon:run {daemonId}';
    protected $description = 'daemon:run';

    public function handle()
    {
        $daemonId = $this->argument('daemonId');
        foreach (config('daemons.list') as $class) {
            if ($class::SIGNATURE == $daemonId) {
                $daemon = new $class();
                $daemon->execute();
                break;
            }
        };
    }
}
