<?php

namespace App\Library\Firmware;

use App\Models\OwHost;
use App\Models\Script;
use App\Library\Script\TranslateDB;
use App\Library\Script\Translators\C as TranslateC;
use App\Models\Property;
use Illuminate\Support\Facades\View;
use DB;

/**
 * Description of Din
 *
 * @author soliton
 */
class Din
{
    /**
     * Din firmware project name
     *
     * @var string
     */
    protected string $project = 'din_master';

    /**
     * Controller name
     *
     * @var string
     */
    protected string $mmcu = 'atmega8a';

    /**
     * Microcontroller page size.
     * Depends on the type of microcontroller and is set manually.
     * Affects pauses when flashing the microcontroller (after transferring one page,
     * you need to pause so that the controller can write to flash)
     *
     * @var int
     */
    protected int $spm_pagesize = 128;

    /**
     * @return int
     */
    public function spmPageSize(): int
    {
        return $this->spm_pagesize;
    }

    /**
     * Path to the folder with firmware sources.
     *
     * @var string
     */
    protected string $rel_path = 'devices/din_master/firmware';

    /**
     *
     */
    public function __construct()
    {
        $settings = Property::getDinSettings();
        $this->mmcu = $settings->mmcu;
        $this->spm_pagesize = config('din.'.$settings->mmcu.'.spm_pagesize');
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

    /**
     * Makes a config file to be included in the firmware.
     * File path: din_master.
     *
     * @return void
     */
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

        foreach ($varList as $row) {
            $row->ow_index = -1;
            if ($row->typ == 'din') {
                $c = array_search($row->channel, ['R1', 'R2', 'R3', 'R4']);
                if ($c !== false) {
                    $row->channel = $c;
                } else {
                    $row->channel = 0;
                }
            } elseif ($row->typ == 'ow') {
                if ($row->host_id) {
                    // Set index OW
                    $owCode = -1;
                    for ($i = 0; $i < count($owList); $i++) {
                        if ($row->host_id == $owList[$i]->id) {
                            $row->ow_index = $i;
                            $owCode = $owList[$i]->rom_1;
                            break;
                        }
                    }

                    // Set channel index
                    if ($owCode > 0) {
                        foreach ($OwHostTyps as $typCode => $devTyp) {
                            if ($typCode == $owCode) {
                                $c = array_search($row->channel, $devTyp['channels']);
                                if ($c !== false) {
                                    $row->channel = $c;
                                } else {
                                    $row->channel = 0;
                                }
                            }
                        }
                    } else {
                        $row->channel = 0;
                    }
                }
            } else {
                $row->channel = 0;
            }
        }

        $variableNames = [];
        foreach ($varList as $row) {
            $variableNames[] = $row->name;
        }

        foreach ($scriptList as &$row) {
            $translator = new TranslateDB($row->data);
            $report = [];
            $row->data_to_c = $translator->run(new TranslateC($variableNames), $report);
        }

        // Setting indexes for variables in events
        foreach ($eventList as &$row) {
            $varIndex = -1;
            for ($i = 0; $i < count($varList); $i++) {
                if ($varList[$i]->id === $row->device_id) {
                    $varIndex = $i;
                    break;
                }
            }
            $row->variableIndex = $varIndex;
        }

        // Checking for the existence of a config directory
        if (!file_exists($this->firmwarePath().'/config')) {
            mkdir($this->firmwarePath().'/config');
        }

        // Pack to file devs.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->firmwarePath().'/config/devs.h', View::make('admin.firmware.din.devs_h', [
            'owList' => $owList,
            'varList' => $varList,
        ]));

        // Pack to file devs.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->firmwarePath().'/config/devs.c', View::make('admin.firmware.din.devs_c', [
            'owList' => $owList,
            'varList' => $varList,
            'varTyps' => [
                'din' => 0,
                'ow' => 1,
                'variable' => 2,
            ],
        ]));

        // Pack to file scripts.h
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->firmwarePath().'/config/scripts.h', View::make('admin.firmware.din.scripts_h', [
            'scriptList' => $scriptList,
        ]));

        // Pack to file scripts.c
        $fs = new \Illuminate\Filesystem\Filesystem();
        $fs->put($this->firmwarePath().'/config/scripts.c', View::make('admin.firmware.din.scripts_c', [
            'scriptList' => $scriptList,
            'eventList' => $eventList,
        ]));
    }

    /**
     * Performs the necessary actions with the avr-gcc compiler to obtain
     * the firmware file in IntelHEX format.
     * The result of the work will be placed in the Release subfolder.
     *
     * @param array $outs
     * @return bool    true - OK; false - ERROR
     */
    public function make(array &$outs): bool
    {
        $firmwarePath = $this->firmwarePath();

        // Getting project files
        $xml = simplexml_load_file($firmwarePath.'/'.$this->project.'.cproj');

        $files = [];
        foreach ($xml->ItemGroup[0]->Compile as $item) {
            $file = (string)$item['Include'];
            if (strpos($file, '.c') === strlen($file) - 2) {
                //if ($file == 'lcd.c') continue;
                $files[] = str_replace('\\', '/', $file);
            }
        }

        $folders = [];
        foreach ($xml->ItemGroup[1]->Folder as $item) {
            $folder = (string)$item['Include'];
            $folders[] = str_replace('\\', '/', $folder);
        }

        // Checking for or creating the necessary directories
        $release_path = $firmwarePath.'/Release';
        if (!file_exists($release_path)) {
            mkdir($release_path);
        }

        // Checking availability or creating subdirectories
        foreach ($folders as $folder) {
            if (!file_exists($release_path.'/'.$folder)) {
                mkdir($release_path.'/'.$folder);
            }
        }

        $commands = [];

        // We collect commands for compiling .c files
        foreach ($files as $file) {
            $path_c = $firmwarePath.'/'.$file;
            $path_o = $release_path.'/'.substr($file, 0, strlen($file) - 2).'.o';
            $commands[] = "avr-gcc -funsigned-char -funsigned-bitfields -Os -fpack-struct -fshort-enums -Wall -c -std=gnu99 -MD -MP -mmcu=$this->mmcu -o $path_o $path_c";
        }

        // Collecting link commands
        $path_map = $release_path.'/'.$this->project.'.map';
        $path_elf = $release_path.'/'.$this->project.'.elf';
        $files_o = [];
        foreach ($files as $file) {
            $files_o[] = $release_path.'/'.substr($file, 0, strlen($file) - 2).'.o';
        }
        $path_o_all = implode(' ', $files_o);
        $commands[] = "avr-gcc -o $path_elf $path_o_all -Wl,-Map=\"$path_map\" -Wl,-lm -mmcu=$this->mmcu ";

        // Firmware creation command
        $path_hex = $release_path.'/'.$this->project.'.hex';
        $commands[] = "avr-objcopy -O ihex -R .eeprom -R .fuse -R .lock -R .signature  $path_elf $path_hex";


        // Statistics commands
        $commands[] = "avr-size -C --mcu=$this->mmcu $path_elf";

        // We launch the created commands for execution
        for ($i = 0; $i < count($commands); $i++) {
            exec($commands[$i].' 2>&1', $outs);
            if (count($outs)) {
                return ($i == count($commands) - 1);
            }
        }

        return false;
    }

    /**
     * We read the firmware file and make an array of 8 bytes per record.
     *
     * @return bool|array
     */
    public function getHex(): bool|array
    {
        $file = $this->firmwarePath().'/Release/'.$this->project.'.hex';
        if (!file_exists($file)) return false;

        $res = [];

        $f = fopen($file, 'r');
        try {
            $d_i = 0;
            $data = [];
            while (!feof($f)) {
                $line = fgets($f);
                $len = hexdec(substr($line, 1, 2));
                for ($i = 0; $i < $len; $i++) {
                    $data[] = hexdec(substr($line, 9 + $i * 2, 2));
                    $d_i++;
                    if ($d_i >= 8) {
                        if (count($data)) {
                            $res[] = $data;
                        }
                        $d_i = 0;
                        $data = [];
                    }
                }
            }
            if (count($data)) {
                $res[] = $data;
            }
        } catch (\Exception $ex) {

        } finally {
            fclose($f);
        }

        return $res;
    }
}
