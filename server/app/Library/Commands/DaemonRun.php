<?php

namespace App\Library\Commands;

class DaemonRun
{
    public function execute(string $daemonId)
    {
        foreach (config('daemons.list') as $class) {
            if ($class::SIGNATURE == $daemonId) {
                $daemon = new $class();
                $daemon->execute();
                break;
            }
        }
    }
}
