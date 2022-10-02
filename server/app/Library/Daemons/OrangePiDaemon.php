<?php

namespace App\Library\Daemons;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\I2cHost;
use \Cron\CronExpression;
use App\Models\Property;
use App\Library\OrangePi\I2c\I2c;

class OrangePiDaemon extends BaseDaemon
{
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

    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $this->printInitPrompt(Lang::get('admin/daemons/orangepi-daemon.description'));

        if (!$this->initialization('orangepi')) return ;

        // Init GPIO pins
        $this->initGPIO();
        // ------------------------

        $lastMinute = \Carbon\Carbon::now()->startOfMinute();
        try {
            while (1) {
                if (!$this->checkEvents()) break;

                // Commands processing
                $command = Property::getOrangePiCommand(true);
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
                    $this->getSystemInfo();
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
     * @return void
     */
    private function initGPIO(): void
    {
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
                                $res = shell_exec('gpioset 0 '.$num.'=1 2>&1');
                            } else {
                                $res = shell_exec('gpioset 0 '.$num.'=0 2>&1');
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
    }

    /**
     *
     * @param string $chan
     * @param int $value
     * @return void
     * @throws \Exception
     */
    private function setValueGPIO(string $chan, int $value): void
    {
        try {
            $channels = config('orangepi.channels');
            $num = $channels[$chan];

            if ($num == -1) return ;

            $res = [];
            if ($value) {
                exec('gpioset 0 '.$num.'=1 2>&1', $res);
            } else {
                exec('gpioset 0 '.$num.'=0 2>&1', $res);
            }
            if (count($res)) {
                throw new \Exception(implode('; ', $res));
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

    /**
     * @return void
     */
    private function getSystemInfo(): void
    {
        try {
            $val = file_get_contents('/sys/devices/virtual/thermal/thermal_zone0/temp');
            $temp = preg_replace("/[^0-9]/", "", $val);

            if ($temp > 200) {
                $temp = round($temp / 1000);
            } else {
                $temp = round($temp);
            }

            foreach ($this->devices as $dev) {
                if ($dev->typ == 'orangepi' && $dev->channel == 'PROC_TEMP') {
                    if (round($dev->value) != $temp) {
                        Device::setValue($dev->id, $temp);
                    }
                    break;
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
        Property::setOrangePiCommandInfo('', true);

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

        Property::setOrangePiCommandInfo(implode("\n", $report));
        Property::setOrangePiCommandInfo('END_SCAN');
    }
}
