<?php

namespace App\Library\Commands;

use App\Library\DaemonManager;
use Illuminate\Support\Facades\Log;

class DaemonsObserve
{
    public function execute()
    {
        $daemonManager = new DaemonManager();
        $started = $daemonManager->findAllStartedDaemons();
        foreach (config('daemons.list') as $daemonClass) {
            try {
                if (!in_array($daemonClass::SIGNATURE, $started)) {
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
