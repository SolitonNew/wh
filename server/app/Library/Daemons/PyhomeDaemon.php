<?php

namespace App\Library\Daemons;

use App\Models\Hub;
use App\Models\Property;
use App\Models\OwHost;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class PyhomeDaemon extends BaseDaemon
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

        $settings = Property::getPyhomeSettings();

        $this->printInitPrompt([
            Lang::get('admin/daemons/pyhome-daemon.description'),
            '--    PORT: '.$settings->port,
            '--    BAUD: '.config('pyhome.baud')
        ]);

        if (!$this->initialization('pyhome')) return ;

        try {
            $settings = Property::getPyhomeSettings();
            $port = $settings->port;
            $baud = config('pyhome.baud');
            exec("stty -F $port $baud cs8 cstopb -icrnl ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts");
            $this->portHandle = fopen($port, 'r+t');
            stream_set_blocking($this->portHandle, false);
            while (!feof($this->portHandle)) {
                $loopErrors = false;
                $command = Property::getPyhomeCommand(true);

                $this->devicesLoopChanges = [];
                // Performing the initial preparation of the iteration
                // It is the same for all controllers.
                switch ($command) {
                    case 'RESET':
                        Property::setPyhomeCommandInfo('', true);
                        break;
                    case 'OW SEARCH':
                        Property::setPyhomeCommandInfo('', true);
                        break;
                    case 'FIRMWARE':
                        Property::setPyhomeCommandInfo('', true);
                        $this->firmwareHex = false;
                        break;
                    default:
                        if (!$this->checkEvents(false, true)) return;
                }

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
                        Property::setPyhomeCommandInfo('END_OW_SCAN');
                        break;
                    case 'CONFIG UPDATE':
                        if (!$loopErrors) {
                            Property::setPyhomeCommandInfo('COMPLETE', true);
                            // Reset the firmware change counter
                            Property::setFirmwareChanges(0);
                        } else {
                            Property::setPyhomeCommandInfo('ERROR', true);
                        }
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
        //
    }

    /**
     * One Wire scan processing command.
     *
     * @param Hub $controller
     * @return void
     */
    private function commandOwSearch(Hub $controller): void
    {
        //
    }

    /**
     * Config update processing command.
     *
     * @param Hub $controller
     * @return bool
     */
    public function commandConfigUpdate(Hub $controller): bool
    {
        //

        return true;
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
        //
        return -1;
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
        return false;
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
}
