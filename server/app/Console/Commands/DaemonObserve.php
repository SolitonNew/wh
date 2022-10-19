<?php

namespace App\Console\Commands;

use App\Library\DaemonManager;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DaemonObserve extends Command
{
    protected $signature = 'daemon:observe';
    protected $description = 'daemon:observe';

    public function handle(DaemonManager $daemonManager)
    {
        foreach (config('daemons.list') as $daemonClass) {
            try {
                if (!$daemonManager->isStarted($daemonClass::SIGNATURE)) {
                    if ($daemonClass::getWorkingState()) {
                        $daemonManager->start($daemonClass::SIGNATURE);
                    }
                }
            } catch (\Exception $ex) {
                Log::channel('daemons')->error($ex->getMessage());
            }
        }
    }
}
