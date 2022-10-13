<?php

namespace App\Library\Daemons;

use App\Library\Firmware\Pyhome;
use App\Models\Hub;
use App\Models\Property;
use App\Models\OwHost;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class PyhomeDaemon extends BaseDaemon
{
    /**
     *
     */
    public const PROPERTY_NAME = 'PYHOME';

    /**
     * @var mixed
     */
    private mixed $portHandle = false;

    /**
     * @var int
     */
    private int $waitCount = 0;

    /**
     * @var string
     */
    private string $inBuffer = '';

    /**
     * @var array
     */
    private array $inServerCommands = [];

    /**
     * @var array
     */
    private array $inVariables = [];

    /**
     * @var array
     */
    private array $inRooms = [];

    /**
     * @var array
     */
    private array $firmwareStatuses = [];

    /**
     * @var array
     */
    private array $devicesLoopChanges = [];

    /**
     *
     */
    const SLEEP_TIME = 50;

    const PACK_SYNC = 1;
    const PACK_COMMAND = 2;
    const PACK_ERROR = 3;

    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $port = static::getSettings('PORT', config('pyhome.default_port'));

        $this->printInitPrompt([
            Lang::get('admin/daemons/pyhome-daemon.description'),
            '--    PORT: '.$port,
            '--    BAUD: '.config('pyhome.baud')
        ]);

        if (!$this->initialization('pyhome')) return ;

        try {
            $baud = config('pyhome.baud');
            exec("stty -F $port $baud cs8 cstopb parodd -icrnl ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts");
            $this->portHandle = fopen($port, 'r+b');
            stream_set_blocking($this->portHandle, false);
            while ($this->portHandle) {
                $loopErrors = false;
                $command = static::getCommand(true);

                $this->devicesLoopChanges = [];
                // Performing the initial preparation of the iteration
                // It is the same for all controllers.
                switch ($command) {
                    case 'RESET':
                        static::setCommandInfo('', true);
                        break;
                    case 'OW SEARCH':
                        static::setCommandInfo('', true);
                        break;
                    case 'FIRMWARE':
                        static::setCommandInfo('', true);
                        break;
                    default:
                        if (!$this->checkEvents(false, true)) return;
                }

                $this->firmwareStatuses = [];

                foreach ($this->hubs as $controller) {
                    switch ($command) {
                        case 'RESET':
                            $this->commandReset($controller);
                            break;
                        case 'OW SEARCH':
                            $this->commandOwSearch($controller);
                            break;
                        case 'CONFIG UPDATE':
                            if (!$this->commandConfigUpdate($controller)) {
                                $loopErrors = true;
                            }
                            break;
                        default:
                            $this->syncVariables($controller);
                    }
                }

                switch ($command) {
                    case 'REBOOT':
                        // not records
                        break;
                    case 'OW SEARCH':
                        static::setCommandInfo('END_OW_SCAN');
                        break;
                    case 'CONFIG UPDATE':
                        break;
                    default:

                }

                usleep(100000);
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            Log::error($s);
            $this->printLine($s);
        } finally {
            if ($this->portHandle) {
                fclose($this->portHandle);
            }
        }
    }

    /**
     * @param Device $device
     * @return void
     */
    protected function deviceChangeValue(Device $device): void
    {
        $this->devicesLoopChanges[] = $device;
    }

    /**
     * Controller reboot processing command.
     *
     * @param $controller
     * @return void
     */
    private function commandReset(Hub $controller): void
    {
        $this->transmitData($controller->rom, self::PACK_COMMAND, ['REBOOT_CONTROLLER', '']);
        $this->readPacks(250);
    }

    /**
     * One Wire scan processing command.
     *
     * @param Hub $controller
     * @return void
     */
    private function commandOwSearch(Hub $controller): void
    {
        $this->inRooms = [];
        $this->transmitData($controller->rom, self::PACK_COMMAND, ['SCAN_ONE_WIRE', '']);
        $this->readPacks(3000);
        $this->transmitData($controller->rom, self::PACK_COMMAND, ['LOAD_ONE_WIRE_ROMS', '']);
        $this->readPacks(500);

        // We got the data. You need to combine them with what is already in
        // the system and issue a report on the operation.

        $new = 0;
        $lost = 0;

        $owOldList = OwHost::whereHubId($controller->id)->get();
        // Finding a lost entries
        foreach ($owOldList as $owOld) {
            $find = false;
            foreach ($this->inRooms as $rom) {
                if ($owOld->rom_1 === $rom[0] &&
                    $owOld->rom_2 === $rom[1] &&
                    $owOld->rom_3 === $rom[2] &&
                    $owOld->rom_4 === $rom[3] &&
                    $owOld->rom_5 === $rom[4] &&
                    $owOld->rom_6 === $rom[5] &&
                    $owOld->rom_7 === $rom[6] &&
                    $owOld->rom_8 === $rom[7]) {
                    $find = true;
                    break;
                }
            }
            if (!$find) {
                $lost++;

                $owOld->lost = 1;
            } else {
                $owOld->lost = 0;
            }
            $owOld->save();
        }

        // Check found entries.
        foreach ($this->inRooms as $rom) {
            $find = false;
            foreach ($owOldList as $owOld) {
                if ($owOld->rom_1 === $rom[0] &&
                    $owOld->rom_2 === $rom[1] &&
                    $owOld->rom_3 === $rom[2] &&
                    $owOld->rom_4 === $rom[3] &&
                    $owOld->rom_5 === $rom[4] &&
                    $owOld->rom_6 === $rom[5] &&
                    $owOld->rom_7 === $rom[6] &&
                    $owOld->rom_8 === $rom[7]) {
                    $find = true;
                    break;
                }
            }
            if (!$find) {
                $new++;
                // Add to the list immediately.
                $ow = new OwHost();
                $ow->hub_id = $controller->id;
                $ow->name = '';
                $ow->comm = '';
                $ow->rom_1 = $rom[0];
                $ow->rom_2 = $rom[1];
                $ow->rom_3 = $rom[2];
                $ow->rom_4 = $rom[3];
                $ow->rom_5 = $rom[4];
                $ow->rom_6 = $rom[5];
                $ow->rom_7 = $rom[6];
                $ow->rom_8 = $rom[7];
                $ow->save();
            }
        }

        $report = [];
        $s = "OW SEARCH. '$controller->name' [TOTAL: ".count($this->inRooms).", NEW: ".$new.", LOST: ".$lost."] ";
        $this->printLine($s);
        $report[] = $s;
        $report[] = str_repeat('-', 35);

        foreach ($this->inRooms as $rom) {
            $a = [];
            foreach ($rom as $b) {
                $a[] = sprintf("x%'02X", $b);
            }
            $s = implode(' ', $a);
            $this->printLine($s);
            $report[] = $s;
        }

        $report[] = str_repeat('-', 35);
        $report[] = '';

        static::setCommandInfo(implode("\n", $report));
    }

    /**
     * Config update processing command.
     *
     * @param Hub $controller
     * @return bool
     */
    public function commandConfigUpdate(Hub $controller): bool
    {
        $ok = false;

        // ------------------------------
        $this->printProgress();
        // ------------------------------

        $firmware = new Pyhome();
        $file = $firmware->getFile();

        $bts = 1024;
        $count = ceil(strlen($file) / $bts);

        $this->transmitData($controller->rom, self::PACK_COMMAND, ['SET_CONFIG_FILE', $count, False]);
        if ($this->readPacks(1000)) {
            $dp = 100 / $count;
            $packs = 0;
            $p = $dp;
            Log::info($dp);
            for ($i = 0; $i < $count; $i++) {
                $part = substr($file,$i * $bts, $bts);
                $this->transmitData($controller->id, self::PACK_COMMAND, ['SET_CONFIG_FILE', $i + 1, $part]);
                $this->readPacks(1000);

                $packs++;
                $this->firmwareStatuses[$controller->id] = round($p);
                // Pack statuses
                $a = [];
                foreach ($this->firmwareStatuses as $cId => $cPerc) {
                    $a[] = $cId.':'.$cPerc;
                }
                static::setCommandInfo(implode(';', $a), true);
                // ------------------------------
                $this->printProgress(round($p));
                // ------------------------------

                $p += $dp;
            }

            $ok = true;
        } else {
            $ok = false;
        }

        sleep(1);

        // Pack statuses
        $this->firmwareStatuses[$controller->id] = $ok ? 'COMPLETE' : 'BAD';
        $a = [];
        foreach ($this->firmwareStatuses as $cId => $cPerc) {
            $a[] = $cId.':'.$cPerc;
        }

        static::setCommandInfo(implode(';', $a), true);

        return $ok;
    }

    /**
     * Devices sync processing and initializing controllers.
     *
     * @param Hub $controller
     * @return void
     */
    private function syncVariables(Hub $controller): void
    {
        //
    }

    /**
     * @param Hub $controller
     * @return void
     */
    private function processingInVariables(Hub $controller): void
    {
        foreach ($this->inVariables as $variable) {
            Device::setValue($variable->id, $variable->value, $controller->id);
        }
    }

    /**
     * @return void
     */
    private function processingInServerCommands(): void
    {
        if (count($this->inServerCommands) == 0) return;

        try {
            for ($i = 0; $i < count($this->inServerCommands);) {
                $w = $this->inServerCommands[$i++];
                $cmd = $w & 0xff;
                $args = (($w & 0xff00) >> 8) - 1;
                $id = $this->inServerCommands[$i++];
                $params = [];
                for ($p = 0; $p < $args; $p++) {
                    $params[] = $this->inServerCommands[$i++];
                }
                $string = \App\Models\ScriptString::find($id);
                if ($string) {
                    $command = '';
                    switch ($cmd) {
                        case 1:
                            $command = "play";
                            break;
                        case 2:
                            $command = "speech";
                            break;
                    }
                    if ($command) {
                        $command .= "('" . $string->data . "'";
                        if (count($params)) {
                            $command .= ', ' . implode(', ', $params);
                        }
                        $command .= ');';

                        \App\Models\Execute::command($command);
                    }
                }

            }
            $this->printLine('   SC   [' . implode(', ', $this->inServerCommands) . ']');
        } catch (\Exception $ex) {
            $this->printLine('Bad server command data. [' . implode(', ', $this->inServerCommands) . ']');
        }
    }

    /**
     * Reading the queue of the incoming packet.
     * An individual timeout value for receiving data is set.
     *
     * @param int $utimeout Allowable waiting time for new data
     * @return bool
     */
    private function readPacks(int $utimeout = 250): bool
    {
        $result = false;
        $this->waitCount = 0;
        while ($this->waitCount < ($utimeout / self::SLEEP_TIME)) {
            $c = fgetc($this->portHandle);
            if ($c !== false) {
                $this->waitCount = 0;
                $this->inBuffer .= $c;
                while (($c = fgetc($this->portHandle)) !== false) {
                    $this->inBuffer .= $c;
                }

                if ($this->processedInBuffer()) {
                    $this->waitCount = 0; // Resets the timeout counter
                    $result = true;
                    if (strlen($this->inBuffer) == 0) break; // Let's not wait for the timeout. We read everything we needed.
                }
            } else {
                usleep(self::SLEEP_TIME * 1000);
                $this->waitCount++;
            }
        }
        return $result;
    }

    /**
     * The main handler for all incoming packets.
     *
     * @return boolean  true -    at least one packet was processed. false - no
     *                            package was found.
     */
    private function processedInBuffer(): bool
    {
        if (!$this->inBuffer) return false;

        $packs = explode(chr(0), $this->inBuffer);

        $result = false;
        for ($i = 0; $i < count($packs); $i++) {
            $pack = json_decode($packs[0], true);
            if (!$pack && $i == 0) return false;
            $result = true;

            if ($pack) {
                array_shift($packs);

                switch ($pack[2][0]) {
                    case 'REBOOT_CONTROLLER':
                        // REBOOT_CONTROLLER
                        break;
                    case 'SCAN_ONE_WIRE':
                        // SCAN_ONE_WIRE
                        break;
                    case 'LOAD_ONE_WIRE_ROMS':
                        $this->inRooms = $pack[2][1];
                        break;
                }
            }
        }

        if (count($packs) > 0) {
            $this->inBuffer = implode(chr(0), $packs);
        } else {
            $this->inBuffer = '';
        }

        return $result;
    }

    /**
     * CRC calculating.
     *
     * @param int $data
     * @return int
     */
    private function crc_table(int $data): int
    {
        $crc = 0x0;
        $fb_bit = 0;
        for ($b = 0; $b < 8; $b++) {
            $fb_bit = ($crc ^ $data) & 0x01;
            if ($fb_bit == 0x01) {
                $crc = $crc ^ 0x18;
            }
            $crc = ($crc >> 1) & 0x7F;
            if ($fb_bit == 0x01) {
                $crc = $crc | 0x80;
            }
            $data >>= 1;
        }
        return $crc;
    }

    /**
     * @param int $controllerROM
     * @param int $packType
     * @param array $packData
     * @return void
     */
    private function transmitData(int $controllerROM, int $packType, array $packData)
    {
        $pack = json_encode([$controllerROM, $packType, $packData]);
        fwrite($this->portHandle, $pack);
        fflush($this->portHandle);
    }
}
