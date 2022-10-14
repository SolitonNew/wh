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
        $varList = DB::select("select v.*, c.rom controller_rom, '' rom
                                 from core_devices v, core_hubs c
                                where v.hub_id = c.id
                               order by v.id");
        foreach ($varList as $var) {
            if ($var->typ == 'ow') {
                foreach ($owList as $ow) {
                    if ($ow->id == $var->host_id) {
                        $a = [
                            $ow->rom_1,
                            $ow->rom_2,
                            $ow->rom_3,
                            $ow->rom_4,
                            $ow->rom_5,
                            $ow->rom_6,
                            $ow->rom_7,
                            $ow->rom_8,
                        ];

                        foreach ($a as &$v) {
                            $c = strtolower(dechex($v));
                            if (strlen($c) == 2) {
                                $v = '0x'.$c;
                            } else {
                                $v = '0x0'.$c;
                            }
                        }

                        $var->rom = implode(', ', $a);
                        break;
                    }
                }
            }
        }
        $scriptList = Script::orderBy('id', 'asc')->get();
        $eventList = DB::select('select d.name deviceName, e.script_id
                                   from core_device_events e, core_devices d
                                  where e.device_id = d.id
                                 order by e.device_id');

        // Pack to file config.py
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->firmwarePath().'/config.py', View::make('admin.firmware.pyhome.config', [
            'varList' => $varList,
            'scriptList' => $scriptList,
            'eventList' => $eventList,
        ]));
    }

    /**
     * @return false|string
     */
    public function getFile()
    {
        return file_get_contents($this->firmwarePath().'/config.py');
    }
}
