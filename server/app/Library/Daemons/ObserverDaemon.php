<?php

namespace App\Library\Daemons;

use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * Description of ObserverDaemon
 *
 * @author soliton
 */
class ObserverDaemon extends BaseDaemon
{
    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $this->printInitPrompt(Lang::get('admin/daemons/observer-daemon.description'));

        $this->initialization();

        while(1) {
            if (!$this->checkEvents()) break;

            usleep(100000);
        }
    }

    /**
     * @param Device $device
     * @return void
     */
    protected function deviceChangeValue(Device $device): void
    {

    }
}
