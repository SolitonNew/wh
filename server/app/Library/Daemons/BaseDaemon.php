<?php

namespace App\Library\Daemons;

use App\Models\WebLogMem;
use App\Models\Property;
use App\Models\Hub;
use App\Models\Device;
use App\Models\EventMem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Library\Script\PhpExecute;
use Illuminate\Support\Facades\Lang;

/**
 * This is the base class for all daemons.
 *
 * @author soliton
 */
class BaseDaemon
{
    /**
     * Signature (id) of the daemon
     * @var string
     */
    protected string $signature = '';

    public function __construct($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @var Collection|array
     */
    protected Collection|array $hubs = [];

    /**
     * @var Collection|array
     */
    protected Collection|array $hubIds = [];

    /**
     * @var Collection|array
     */
    protected Collection|array $devices = [];

    /**
     * @var int
     */
    private int $lastEventID = -1;

    /**
     * @var string|bool
     */
    private string|bool $daemonHubTyp = false;

    /**
     * @param string $typ
     * @return bool
     */
    protected function initialization(string $typ = ''): bool
    {
        $this->daemonHubTyp = $typ;

        if (!$this->initializationHubs()) return false;
        $this->initializationHosts();
        $this->initializationDevices();

        $this->lastEventID = EventMem::max('id') ?? -1;

        return true;
    }

    /**
     *
     * @return bool
     */
    protected function initializationHubs(): bool
    {
        if ($this->daemonHubTyp === '') return true;

        $this->hubs = Hub::where('id', '>', 0)
            ->whereTyp($this->daemonHubTyp)
            ->orderBy('rom', 'asc')
            ->get();

        if (count($this->hubs) == 0) {
            $this->printLine("[".parse_datetime(now())."] WARNING! Hubs not found. The demon stopped.");
            $this->disableAutorun();
            return false;
        }

        $this->hubIds = $this->hubs
            ->pluck('id')
            ->toArray();

        return true;
    }

    /**
     * @return void
     */
    protected function initializationHosts(): void
    {
        //
    }

    /**
     * @return void
     */
    protected function initializationDevices(): void
    {
        $this->devices = Device::orderBy('id')
            ->get();
    }

    /**
     * The launch of this method is automated.
     * Each inheritor of this class must override it and place it inside
     * the code that the daemon should execute.
     */
    public function execute(): void
    {
        while (1) {
            if (!$this->checkEvents()) break;

            usleep(200000);
        }
    }

    /**
     * Must be called in the main daemon loop
     *
     * @param bool $withScripts
     * @param bool $noCheckValue
     * @return bool
     */
    protected function checkEvents(bool $withScripts = true, bool $noCheckValue = false): bool
    {
        $changes = EventMem::where('id', '>', $this->lastEventID)
                    ->orderBy('id', 'asc')
                    ->get();

        foreach ($changes as $change) {
            $this->lastEventID = $change->id;
            if ($change->typ == EventMem::DEVICE_CHANGE_VALUE) {
                foreach ($this->devices as $device) {
                    if ($device->id == $change->device_id) {
                        if ($noCheckValue || $device->value != $change->value) {
                            // Store new device value
                            $device->value = $change->value;
                            $device->valueFromID = $change->from_id;
                            $device->lastDeviceChangesID = $change->device_changes_id;

                            // Call change value handler
                            $this->deviceChangeValue($device);

                            // Run event script if it's attached
                            if ($withScripts) {
                                $this->executeEvents($device);
                            }
                        }
                        break;
                    }
                }
            } else {
                switch ($change->typ) {
                    case EventMem::HUB_LIST_CHANGE:
                        if (!$this->initializationHubs()) return false;
                        break;
                    case EventMem::HOST_LIST_CHANGE:
                        $this->initializationHosts();
                        break;
                    case EventMem::DEVICE_LIST_CHANGE:
                        $this->initializationDevices();
                        break;
                }
            }
        }

        return true;
    }

    /**
     * @param Device $device
     */
    protected function executeEvents(Device &$device): void
    {
        if (!in_array($device->hub_id, $this->hubIds)) return;

        $sql = "select s.comm, s.data
                  from core_device_events de, core_scripts s
                 where de.device_id = ".$device->id."
                   and de.script_id = s.id";

        foreach (DB::select($sql) as $script) {
            try {
                $execute = new PhpExecute($script->data);
                $execute->run();
                $s = "[".parse_datetime(now())."] RUN SCRIPT '".$script->comm."' \n";
                $this->printLine($s);
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR\n";
                $s .= $ex->getMessage();
                $this->printLine($s);
            }
        }
    }

    /**
     * Can be overloaded to track device value changes.
     *
     * @param Device $device
     */
    protected function deviceChangeValue(Device $device): void
    {
        // For inheriting
    }

    /**
     * This method of adding a log entry into DB.
     *
     * @param string $text
     */
    public function printLine($text): void
    {
        try {
            $item = new WebLogMem();
            $item->daemon = $this->signature;
            $item->data = $text;
            $item->save();

            echo "$text\n";
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
    }

    /**
     *
     * @param string $text
     */
    public function printLineToLast($text): void
    {
        try {
            $item = WebLogMem::whereDaemon($this->signature)
                ->orderBy('id', 'desc')
                ->first();
            if ($item) {
                $item->data = $text;
                $item->save();
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage()."\n";
        }
    }

    /**
     *
     * @param int $percent
     */
    public function printProgress(int $percent = 0): void
    {
        if ($percent == 0) {
            $this->printLine('PROGRESS:0');
        } else {
            $this->printLineToLast('PROGRESS:'.$percent);
        }
    }

    /**
     * @param string|array $text
     * @return void
     */
    public function printInitPrompt(string|array $text): void
    {
        $this->printLine('');
        $this->printLine('');
        $this->printLine(str_repeat('-', 100));
        if (is_array($text)) {
            foreach (array_reverse($text) as $line) {
                $this->printLine($line);
            }
        } else {
            $this->printLine($text);
        }
        $this->printLine('-- ['.parse_datetime(now()).'] '.str_repeat('-', 75));
        $this->printLine('');
    }

    /**
     * Disabling autorun of this daemon
     */
    public function disableAutorun(): void
    {
        Property::setAsStoppedDaemon($this->signature);
    }
}
