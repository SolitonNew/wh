<?php

namespace App\Library\Firmware;

use App\Models\OwHost;
use App\Models\Script;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class Pyhome
{
    /**
     * Path to the folder with firmware sources.
     *
     * @var string
     */
    protected string $rel_path = 'devices/pyhome';

    public function __construct()
    {

    }

    /**
     * Absolute path to the firmware directory.
     *
     * @return string
     */
    protected function firmwarePath(): string
    {
        $path = explode('/', base_path());
        array_pop($path);
        $path[] = $this->rel_path;
        return implode('/', $path);
    }

    public function generateConfig(): void
    {
        // Reading all the necessary data
        $OwHostTyps = config('onewire.types');
        $owList = OwHost::orderBy('id', 'asc')->get();
        $varList = DB::select("select v.*, c.rom controller_rom
                                 from core_devices v, core_hubs c
                                where v.hub_id = c.id
                               order by v.id");
        $scriptList = Script::orderBy('id', 'asc')->get();
        $eventList = DB::select('select e.device_id, GROUP_CONCAT(e.script_id) script_ids
                                   from core_device_events e
                                 group by e.device_id
                                 order by e.device_id');


        $devices = [];

        // Pack to file config.py
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->firmwarePath().'/config.py', View::make('admin.firmware.pyhome.config', [
            'varList' => $varList,
        ]));
    }
}
