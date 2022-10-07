<?php

namespace App\Library\Daemons;

use App\Models\Hub;
use App\Models\Property;
use App\Models\OwHost;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class DinDaemon extends BaseDaemon
{
    /**
     * @var mixed
     */
    private mixed $portHandle = false;

    /**
     * @var int
     */
    private int $waitCount = 0;

    /**
     * @var int
     */
    private int $inPackCount = 0;

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
     * @var array|bool
     */
    private array|bool $firmwareHex = [];

    /**
     * @var int
     */
    private int $firmwareSpmPageSize = 0;

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

    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $settings = Property::getDinSettings();

        $this->printInitPrompt([
            Lang::get('admin/daemons/din-daemon.description'),
            '--    PORT: '.$settings->port,
            '--    BAUD: '.config('din.'.$settings->mmcu.'.baud')
        ]);

        if (!$this->initialization('din')) return ;

        try {
            $settings = Property::getDinSettings();
            $port = $settings->port;
            $baud = config('din.'.$settings->mmcu.'.baud');
            exec("stty -F $port $baud cs8 cstopb -icrnl ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts");
            $this->portHandle = fopen($port, 'r+b');
            stream_set_blocking($this->portHandle, false);
            while (!feof($this->portHandle)) {
                $loopErrors = false;
                $command = Property::getDinCommand(true);

                $this->devicesLoopChanges = [];
                // Performing the initial preparation of the iteration
                // It is the same for all controllers.
                switch ($command) {
                    case 'RESET':
                        Property::setDinCommandInfo('', true);
                        break;
                    case 'OW SEARCH':
                        Property::setDinCommandInfo('', true);
                        break;
                    case 'FIRMWARE':
                        Property::setDinCommandInfo('', true);
                        $this->firmwareHex = false;
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
                        case 'FIRMWARE':
                            if (!$this->commandFirmware($controller)) {
                                $loopErrors = true;
                            }
                            break;
                        default:
                            $this->syncVariables($controller);
                    }
                }

                switch ($command) {
                    case 'RESET':
                        // not records
                        break;
                    case 'OW SEARCH':
                        Property::setDinCommandInfo('END_OW_SCAN');
                        break;
                    case 'FIRMWARE':
                        /*if (!$loopErrors) {
                            Property::setDinCommandInfo('COMPLETE', true);
                            // Reset the firmware change counter
                            Property::setFirmwareChanges(0);
                        } else {
                            Property::setDinCommandInfo('ERROR', true);
                        }*/
                        $this->firmwareHex = false;
                        break;
                    default:

                }

                usleep(100000);
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
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
        $this->readPacks(250);
        $this->transmitCMD($controller->rom, 1, 0);
        usleep(250000);
    }

    /**
     * One Wire scan processing command.
     *
     * @param Hub $controller
     * @return void
     */
    private function commandOwSearch(Hub $controller): void
    {
        $this->readPacks(250);
        $this->inRooms = [];
        $this->transmitCMD($controller->rom, 7, 0);
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

        Property::setDinCommandInfo(implode("\n", $report));
    }

    /**
     * Firmware processing command.
     *
     * @param Hub $controller
     * @return bool
     */
    public function commandFirmware(Hub $controller): bool
    {
        // ------------------------------
        $this->printProgress();
        // ------------------------------
        if (!$this->firmwareHex) {
            $firmware = new \App\Library\Firmware\Din();
            $this->firmwareHex = $firmware->getHex();
            $this->firmwareSpmPageSize = $firmware->spmPageSize();
        }

        $PAGE_STORE_PAUSE = 150000;

        $count = count($this->hubs);
        $index = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($this->hubs[$i]->id == $controller->id) {
                $index = $i;
                break;
            }
        }

        $this->readPacks(250);
        $this->transmitCMD($controller->rom, 1, 0);

        usleep(1000000);

        $this->transmitCMD($controller->rom, 24, count($this->firmwareHex));

        $hexPackStep = ceil($this->firmwareSpmPageSize / 8);

        $dp = 100 / count($this->firmwareHex);
        $packs = 0;
        $p = 0;
        foreach ($this->firmwareHex as $hex) {
            $this->transmitHEX($controller->rom, $hex);
            $packs++;
            if ($packs % $hexPackStep == 0) {
                $this->firmwareStatuses[$controller->id] = round($p);
                // Pack statuses
                $a = [];
                foreach ($this->firmwareStatuses as $cId => $cPerc) {
                    $a[] = $cId.':'.$cPerc;
                }
                Property::setDinCommandInfo(implode(';', $a), true);
                // ------------------------------
                $this->printProgress(round($p));
                // ------------------------------
                usleep($PAGE_STORE_PAUSE);
            }
            $p += $dp;
        }

        usleep($PAGE_STORE_PAUSE);

        $this->transmitCMD($controller->rom, 25, count($this->firmwareHex));
        $this->readPacks(750);

        $ok = ($this->inPackCount == count($this->firmwareHex));
        $this->inPackCount = 0;

        // Pack statuses
        $this->firmwareStatuses[$controller->id] = $ok ? 100 : 'BAD';
        $a = [];
        foreach ($this->firmwareStatuses as $cId => $cPerc) {
            $a[] = $cId.':'.$cPerc;
        }
        Property::setDinCommandInfo(implode(';', $a), true);

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
        $stat = 'OK';
        $vars_out = [];
        $errorText = '';
        try {
            // Send command "prepare to receive"
            $this->transmitCMD($controller->rom, 2, count($this->devicesLoopChanges));

            // Send device values
            foreach ($this->devicesLoopChanges as $device) {
                if ($device->valueFromID !== $controller->id) {
                    $this->transmitVAR($controller->rom, $device->id, $device->value);
                    $vars_out[] = "$device->id: $device->value";
                }
            }

            // Send command "prepare to give your changes"
            $this->transmitCMD($controller->rom, 3, 0);

            $this->inVariables = [];
            $this->inServerCommands = [];
            // Waiting for a controller's response.
            switch ($this->readPacks(250)) {
                case 5: // Controller request of the initialization data
                    $stat = 'INIT';
                    $vars_out = [];
                    $this->transmitCMD($controller->rom, 6, count($this->devices));
                    $counter = 0;
                    foreach ($this->devices as $device) {
                        $this->transmitVAR($controller->rom, $device->id, $device->value);
                        $vars_out[] = "$device->id: $device->value";
                        if ($counter++ > 5) {
                            usleep(75000); // We slow down periodically. The controller on the other end is not powerful.
                            $counter = 0;
                        }
                    }
                    $this->readPacks(250);
                    break;
                case 26: // The controller asked for the firmware
                    $stat = 'FIRMWARE QUERY';
                    break;
                case 27: // Controller in boot (probably overloaded)
                    $stat = 'BOOTLOADER';
                    break;
                case -1:
                    $this->inBuffer = '';
                    throw new \Exception('Controller did not respond');
                default:
                    // Saving variables data
                    $this->processingInVariables($controller);

                    // Processing server commands
                    $this->processingInServerCommands();
            }
        } catch (\Exception $ex) {
            $stat = 'ERROR';
            $errorText = $ex->getMessage();
        }

        $this->printLine("[".parse_datetime(now())."] SYNC. '$controller->name': $stat");
        if ($stat == 'OK') {
            foreach (array_chunk($vars_out, 15) as $key => $chunk) {
                if ($key == 0) {
                    $s = '   >>   ';
                } else {
                    $s = '        ';
                }
                $this->printLine($s.'['.implode(', ', $chunk).']');
            }
            if (!count($vars_out)) {
                $this->printLine('   >>   []');
            }

            $vars_in = [];
            foreach ($this->inVariables as $variable) {
                $vars_in[] = "$variable->id: $variable->value";
            }

            $this->printLine("   <<   [".implode(', ', $vars_in)."]");
        } elseif ($stat == 'INIT') {
            foreach (array_chunk($vars_out, 15) ?? [] as $key => $chunk) {
                if ($key == 0) {
                    $s = '   >>   ';
                } else {
                    $s = '        ';
                }
                $this->printLine($s.'['.implode(', ', $chunk).']');
            }
            if (!count($vars_out)) {
                $this->printLine('   >>   []');
            }
        } elseif ($stat == 'ERROR') {
            $this->printLine($errorText);
        } elseif ($stat == 'IN BOOTLOADER') {
            //
        } elseif ($stat == 'FIRMWARE QUERY') {
            if ($this->commandFirmware($controller)) {
                $this->printLine('FIRMWARE OK');
            } else {
                $this->printLine('FIRMWARE ERROR');
            }
        }
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
        if (count($this->inServerCommands) == 0) return ;

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
                        $command .= "('".$string->data."'";
                        if (count($params)) {
                            $command .= ', '.implode(', ', $params);
                        }
                        $command .= ');';

                        \App\Models\Execute::command($command);
                    }
                }

            }
            $this->printLine('   SC   ['.implode(', ', $this->inServerCommands).']');
        } catch (\Exception $ex) {
            $this->printLine('Bad server command data. ['.implode(', ', $this->inServerCommands).']');
        }
    }

    /**
     * Reading the queue of the incoming packet.
     * An individual timeout value for receiving data is set.
     *
     * @param int $utimeout  Allowable waiting time for new data
     * @return int    -1 - no data received. >= 0 - received something
     */
    private function readPacks(int $utimeout = 250): int
    {
        $returnCmd = -1;
        $this->waitCount = 0;
        while ($this->waitCount < ($utimeout / self::SLEEP_TIME)) {
            $c = fgetc($this->portHandle);
            if ($c !== false) {
                $this->waitCount = 0;
                $this->inBuffer .= $c;
                $cmd = 0;
                if ($this->processedInBuffer($cmd)) {
                    $this->waitCount = 0; // Resets the timeout counter
                    if ($cmd > 0) {
                        $returnCmd = $cmd;
                    } else {
                        $returnCmd = 0;
                    }

                    if ($this->inPackCount <= 0) break; // Let's not wait for the timeout. We read everything we needed.
                }
            } else {
                usleep(self::SLEEP_TIME * 1000);
                $this->waitCount++;
            }
        }
        return $returnCmd;
    }

    /**
     * The main handler for all incoming packets.
     *
     * @param integer $returnCmd  the code of the last command processed in this
     *                            iteration. If there were no commands, there
     *                            will be 0.
     * @return boolean  true -    at least one packet was processed. false - no
     *                            package was found.
     */
    private function processedInBuffer(int &$returnCmd): bool
    {
        $returnCmd = 0;
        $result = false;

        start_loop:

        if (strlen($this->inBuffer) < 7) return $result;

        $sign = unpack('a*', $this->inBuffer[0].$this->inBuffer[1].$this->inBuffer[2])[1];
        $size = 0;
        switch ($sign) {
            case 'INT':
                if (strlen($this->inBuffer) < 7) return $result;
                $size = 7;
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->crc_table($crc ^ ord($this->inBuffer[$i]));
                }
                if ($crc === 0) {
                    $returnCmd = 0;
                    $controller = unpack('C', $this->inBuffer[3])[1];
                    $data = unpack('s', $this->inBuffer[4].$this->inBuffer[5])[1];
                    $this->inServerCommands[] = $data;
                    $this->inPackCount--;
                } else {
                    $size = 0;
                }
                break;
            case 'CMD':
                if (strlen($this->inBuffer) < 8) return $result;
                $size = 8;
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->crc_table($crc ^ ord($this->inBuffer[$i]));
                }
                if ($crc === 0) {
                    $this->inPackCount = 0;
                    $controller = unpack('C', $this->inBuffer[3])[1];
                    $cmd = unpack('C', $this->inBuffer[4])[1];
                    $tag = unpack('s', $this->inBuffer[5].$this->inBuffer[6])[1];
                    if ($cmd === 4) { // This is a package that indicates how much to read after
                        $this->inPackCount = $tag;
                    }
                    $returnCmd = $cmd;
                } else {
                    $size = 0;
                }
                break;
            case 'VAR':
                if (strlen($this->inBuffer) < 9) return $result;
                $size = 9;
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->crc_table($crc ^ ord($this->inBuffer[$i]));
                }
                if ($crc === 0) {
                    $returnCmd = 0;
                    $controller = unpack('C', $this->inBuffer[3])[1];
                    $id = unpack('s', $this->inBuffer[4].$this->inBuffer[5])[1];
                    $value = unpack('s', $this->inBuffer[6].$this->inBuffer[7])[1];
                    $value = $value / 10;
                    $this->inVariables[] = (object)[
                        'id' => $id,
                        'value' => $value,
                    ];
                    $this->inPackCount--;
                } else {
                    $size = 0;
                }
                break;
            case 'ROM':
                if (strlen($this->inBuffer) < 13) return $result;
                $size = 13;
                $controller = unpack('C', $this->inBuffer[3])[1];
                $crc = 0;
                for ($i = 0; $i < $size; $i++) {
                    $crc = $this->crc_table($crc ^ ord($this->inBuffer[$i]));
                }
                if ($crc === 0) {
                    $returnCmd = 0;
                    $rom = [];
                    for ($i = 0; $i < 8; $i++) {
                        $rom[] = unpack('C', $this->inBuffer[4 + $i])[1];
                    }
                    $this->inRooms[] = $rom;
                    $this->inPackCount--;
                } else {
                    $size = 0;
                }
                break;
            default:
                $sign = '';
        }

        if ($sign == '' || $size === 0) {
            for ($i = 1; $i < strlen($this->inBuffer) - 2; $i++) {
                if ($this->inBuffer[$i] >= 'A' &&
                    $this->inBuffer[$i + 1] >= 'A' &&
                    $this->inBuffer[$i + 2] >= 'A') {
                    $size = $i;
                    break;
                }
            }
        }

        if ($size === 0) {
            $this->inBuffer = '';
            return $result;
        } elseif ($size === strlen($this->inBuffer)) {
            $this->inBuffer = '';
            return true;
        } else {
            $this->inBuffer = substr($this->inBuffer, $size);
            $result = true;
            goto start_loop;
        }
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
     * @param int $cmd
     * @param int $tag
     */
    private function transmitCMD(int $controllerROM, int $cmd, int $tag)
    {
        $pack = pack('a*', 'CMD');
        $pack .= pack('C', $controllerROM);
        $pack .= pack('C', $cmd);
        $pack .= pack('s', $tag);
        $crc = 0x0;
        for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->crc_table($crc ^ ord($pack[$i]));
        }
        $pack .= pack('C', $crc);
        fwrite($this->portHandle, $pack);
        fflush($this->portHandle);
    }

    /**
     * @param int $controllerROM
     * @param int $id
     * @param float $value
     */
    private function transmitVAR(int $controllerROM, int $id, float $value)
    {
        $pack = pack('a*', 'VAR');
        $pack .= pack('C', $controllerROM);
        $pack .= pack('s', $id);
        $pack .= pack('s', ceil($value * 10));
        $crc = 0x0;
        for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->crc_table($crc ^ ord($pack[$i]));
        }
        $pack .= pack('C', $crc);
        fwrite($this->portHandle, $pack);
        fflush($this->portHandle);
    }

    /**
     * @param int $controllerROM
     * @param array $data
     */
    private function transmitHEX(int $controllerROM, array $data)
    {
        $pack = pack('a*', 'HEX');
        $pack .= pack('C', $controllerROM);
        for ($i = 0; $i < 8; $i++) {
            $pack .= pack('C', isset($data[$i]) ? $data[$i] : 0xff);
        }
        $crc = 0x0;
        for ($i = 0; $i < strlen($pack); $i++) {
            $crc = $this->crc_table($crc ^ ord($pack[$i]));
        }
        $pack .= pack('C', $crc);
        fwrite($this->portHandle, $pack);
        fflush($this->portHandle);

        usleep(10000); // Otherwise, the controller does not have time to process.
    }
}
