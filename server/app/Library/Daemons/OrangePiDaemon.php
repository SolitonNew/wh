<?php

namespace App\Library\Daemons;

use App\Models\Device;
use App\Models\Hub;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\I2cHost;
use \Cron\CronExpression;
use App\Models\Property;
use App\Library\OrangePi\I2c\I2c;
use Illuminate\Support\Facades\Log;

class OrangePiDaemon extends BaseDaemon
{
    public const SIGNATURE = 'orangepi-daemon';

    /**
     *
     */
    public const PROPERTY_NAME = 'ORANGEPI';

    /**
     * @var int|bool
     */
    private int|bool $prevExecuteHostI2cTime = false;

    /**
     * @var Collection|array
     */
    private Collection|array $i2cHosts = [];

    /**
     * @var array
     */
    private array $i2cDrivers = [];

    private bool $gpioEnabled = false;

    /**
     * @return bool
     */
    public static function canRun(): bool
    {
        return (Hub::whereTyp('orangepi')->count() > 0);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $this->printInitPrompt(Lang::get('admin/daemons/orangepi-daemon.description'));

        if (!$this->initialization('orangepi')) return ;

        // Init GPIO pins
        $this->gpioEnabled = $this->initGPIO();
        if (!$this->gpioEnabled) {
            $this->printLine(Lang::get('admin/daemons/orangepi-daemon.gpio_disabled_message'));
        }
        // ------------------------

        $lastMinute = \Carbon\Carbon::now()->startOfMinute();
        try {
            while (1) {
                if (!$this->checkEvents()) break;

                // Commands processing
                $command = static::getCommand(true);
                switch ($command) {
                    case 'SCAN':
                        $this->scanNetworks();
                        break;
                }

                // I2c hosts
                $this->processingI2cHosts();

                // Get Orange Pi system info
                $minute = \Carbon\Carbon::now()->startOfMinute();
                if ($minute->gt($lastMinute)) {
                    $this->loadProcessorTemperature();
                    $this->loadMemoryState();
                }
                $lastMinute = $minute;
                // -----------------------------

                usleep(100000);
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s);
        } finally {

        }
    }

    /**
     * @return void
     */
    protected function initializationHosts(): void
    {
        $this->i2cDrivers = config('orangepi.drivers');

        $this->i2cHosts = I2cHost::whereIn('hub_id', $this->hubIds)
            ->get();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function initGPIO(): bool
    {
        if (!file_exists('/bin/gpioset')) {
            return false;
        }

        $gpio = config('orangepi.gpio');
        $channels = config('orangepi.channels');

        $enabled = [];
        $errors = [];

        foreach ($channels as $chan => $num) {
            if ($num > -1) {
                try {
                    $res = '';
                    foreach ($this->devices as $device) {
                        if (in_array($device->hub_id, $this->hubIds) && $device->channel == $chan) {
                            if ($device->value) {
                                $res = shell_exec('gpioset '.$gpio.' '.$num.'=1 2>&1');
                            } else {
                                $res = shell_exec('gpioset '.$gpio.' '.$num.'=0 2>&1');
                            }
                            break;
                        }
                    }

                    if ($res) {
                        throw new \Exception($res);
                    }

                    $enabled[] = $chan;
                } catch (\Exception $ex) {
                    $errors[$chan] = $ex->getMessage();
                }
            }
        }

        $this->printLine('GPIO ['.implode(', ', $enabled).'] ENABLED');
        if (count($errors)) {
            foreach ($errors as $chan => $error) {
                $this->printLine('GPIO ['.$chan.'] INIT ERROR: '.$error);
            }
        }

        $this->printLine(str_repeat('-', 100));

        return true;
    }

    /**
     * @param string $chan
     * @param float $value
     * @return void
     */
    private function setValueGPIO(string $chan, float $value): void
    {
        if (!$this->gpioEnabled) {
            return ;
        }

        try {
            $gpio = config('orangepi.gpio');
            $channels = config('orangepi.channels');
            $num = $channels[$chan];

            if ($num == -1) return ;

            if ($value) {
                $res = shell_exec('gpioset '.$gpio.' '.$num.'=1 2>&1');
            } else {
                $res = shell_exec('gpioset '.$gpio.' '.$num.'=0 2>&1');
            }
            if ($res) {
                throw new \Exception($res);
            }
            $this->printLine('['.parse_datetime(now()).'] GPIO ['.$chan.'] SET VALUE: '.($value ? 'ON' : 'OFF'));
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s);
        }
    }

    /**
     * @param Device $device
     * @return void
     * @throws \Exception
     */
    protected function deviceChangeValue(Device $device): void
    {
        if (in_array($device->hub_id, $this->hubIds) && $device->typ == 'orangepi') {
            $this->setValueGPIO($device->channel, $device->value);
        }
    }

    private function setOrangePiDeviceValueByChannel(string $channel, float $value)
    {
        $roundValue = round($value);

        foreach ($this->devices as $dev) {
            if ($dev->typ == 'orangepi' && $dev->channel === $channel) {
                if (round($dev->value) != $roundValue) {
                    Device::setValue($dev->id, $value);
                }
                break;
            }
        }
    }

    /**
     * @return void
     */
    private function loadProcessorTemperature()
    {
        try {
            $val = file_get_contents('/sys/devices/virtual/thermal/thermal_zone0/temp');
            $temp = preg_replace("/[^0-9]/", "", $val);

            if ($temp > 200) {
                $temp = round($temp / 1000);
            } else {
                $temp = round($temp);
            }

            $this->setOrangePiDeviceValueByChannel('PROC_TEMP', $temp);
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s);
        }
    }

    /**
     * @return void
     */
    private function loadMemoryState()
    {
        try {
            $info = file_get_contents('/proc/meminfo');
            foreach (explode("\n", $info) as $line) {
                if (str_starts_with($line, 'MemTotal:')) {
                    $value = preg_replace("/[^0-9]/", "", $line);
                    $this->setOrangePiDeviceValueByChannel('MEM_TOTAL', round($value / 1024));
                } else
                if (str_starts_with($line, 'MemFree:')) {
                    $value = preg_replace("/[^0-9]/", "", $line);
                    $this->setOrangePiDeviceValueByChannel('MEM_FREE', round($value / 1024));
                }
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s);
        }
    }

    /**
     * @return void
     */
    private function processingI2cHosts(): void
    {
        if (!$this->gpioEnabled) return ;

        $now = floor(\Carbon\Carbon::now()->timestamp / 60);

        // Checking for execute after daemon restart.
        if ($this->prevExecuteHostI2cTime === false) {
            $this->prevExecuteHostI2cTime = $now;
            return ;
        }

        // Checking for execute at ever minutes.
        if ($now == $this->prevExecuteHostI2cTime) {
            return ;
        }

        // Storing the previous time value
        $this->prevExecuteHostI2cTime = $now;

        $outData = [];

        foreach ($this->i2cHosts as $host) {
            if (!isset($this->i2cDrivers[$host->typ])) continue;

            $cron = $this->i2cDrivers[$host->typ]['cron'];
            if (CronExpression::factory($cron)->isDue()) {
                $class = $this->i2cDrivers[$host->typ]['class'];
                try {
                    $driver = new $class($host->address);
                    $result = $driver->getData();

                    if ($result) {
                        foreach ($result as $chan => $val) {
                            foreach ($this->devices as $dev) {
                                if ($dev->host_id == $host->id &&
                                    $dev->typ == 'i2c' &&
                                    $dev->channel == $chan &&
                                    $dev->value != $val)
                                {
                                    Device::setValue($dev->id, $val);
                                    $outData[] = $dev->id.': '.$val;
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $ex) {
                    $s = "[".parse_datetime(now())."] ERROR\n";
                    $s .= $ex->getMessage();
                    $this->printLine($s);
                }
            }
        }

        if (count($outData)) {
            $s = "[".parse_datetime(now())."] I2C [".implode(", ", $outData)."]";
            $this->printLine($s);
        }
    }

    /**
     * @return void
     */
    private function scanNetworks(): void
    {
        OrangePiDaemon::setCommandInfo('', true);

        if (!$this->gpioEnabled) {
            OrangePiDaemon::setCommandInfo(Lang::get('admin/daemons/orangepi-daemon.gpio_disabled_message'));
            OrangePiDaemon::setCommandInfo('END_SCAN');
            return ;
        }

        $addresses = I2c::scan();

        $new = 0;
        $lost = 0;

        $oldHosts = $this->i2cHosts;

        // Finding a lost entries
        foreach ($oldHosts as $oldHost) {
            if (!in_array($oldHost->address, $addresses)) {
                $lost++;
                $oldHost->lost = 1;
            } else {
                $oldHost->lost = 0;
            }
            $oldHost->save();
        }

        // Check found entries.
        foreach ($addresses as $addr) {
            $find = false;
            foreach ($oldHosts as $oldHost) {
                if ($addr == $oldHost->address) {
                    $find = true;
                    break;
                }
            }

            if (!$find) {
                $new++;
                // Add to the list immediately.
                // ...
            }
        }

        $report = [];
        $s = "I2C SEARCH. [TOTAL: ".count($addresses).", NEW: ".$new.", LOST: ".$lost."] ";
        $this->printLine($s);
        $report[] = $s;
        $report[] = str_repeat('-', 35);
        foreach ($addresses as &$addr) {
            $report[] = sprintf("x%'02X", $addr);
        }
        $report[] = str_repeat('-', 35);
        $report[] = '';

        OrangePiDaemon::setCommandInfo(implode("\n", $report));
        OrangePiDaemon::setCommandInfo('END_SCAN');
    }
}
