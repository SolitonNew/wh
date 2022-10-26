<?php

namespace App\Library\Daemons;

use App\Models\Device;
use App\Models\Hub;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\I2cHost;
use \Cron\CronExpression;
use App\Library\OrangePi\I2c\I2c;
use App\Library\OrangePi\Gpio;
use App\Library\OrangePi\System;

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
     * I2c Drivers
     * 
     * @var array
     */
    private array $i2cDrivers = [];
    
    /**
     * Gpio Driver
     * 
     * @var Gpio
     */
    private null|Gpio $gpio = null;
    
    /**
     * System Control Driver
     * 
     * @var System
     */
    private null|System $system = null;

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
        
        // Init GPIO driver
        try {
            $this->gpio = new Gpio(config('orangepi.gpio'), config('orangepi.channels'));
            $this->initGpioDevices();
        } catch (\Exception $ex) {
            $this->printLine(Lang::get('admin/daemons/orangepi-daemon.gpio_disabled_message'));
        }
        
        // Init System Control driver
        try {
            $this->system = new System();
            foreach ($this->system->checkSources() as $line) {
                $this->printLine($line);
            }
        } catch (\Exception $ex) {
            $this->printLine(Lang::get('admin/daemons/orangepi-daemon.system_disabled_message'));
        }

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
                    $this->processingSystem();
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
    private function initGpioDevices(): void
    {
        $values = [];
        foreach ($this->devices as $device) {
            if (in_array($device->hub_id, $this->hubIds)) {
                $values[$device->channel] = $device->value;
            }
        }
        $this->gpio->init($values);
        $this->printLine('GPIO ENABLED');
    }
    
    /**
     * @return void
     */
    private function processingSystem(): void
    {
        if (!$this->system) return ;
        
        // Processor Temperature
        try {
            $temp = $this->system->getProcessorTemperature();
            if ($temp > 0) {
                $this->setOrangePiDeviceValueByChannel('PROC_TEMP', $temp);
            }
        } catch (\Exception $ex) {
            $s = "[".parse_datetime(now())."] ERROR\n";
            $s .= $ex->getMessage();
            $this->printLine($s);
        }
        
        // Memory Status        
        try {
            list($total, $free) = $this->system->getMemoryStatus();
            $this->setOrangePiDeviceValueByChannel('MEM_TOTAL', $total);
            $this->setOrangePiDeviceValueByChannel('MEM_FREE', $free);
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
        if (in_array($device->hub_id, $this->hubIds) && ($device->typ == 'orangepi')) {
            if ($this->gpio && $this->gpio->isPinChannel($device->channel)) {
                try {
                    $this->gpio->set($device->channel, $device->value);
                    $this->printLine('['.parse_datetime(now()).'] GPIO ['.($device->channel.'] SET VALUE: '.($device->value ? 'ON' : 'OFF')));
                } catch (\Exception $ex) {
                    $s = "[".parse_datetime(now())."] ERROR\n";
                    $s .= $ex->getMessage();
                    $this->printLine($s);
                }
            }
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
    private function processingI2cHosts(): void
    {
        if (!$this->gpio) return ;

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

        if (!$this->gpio) {
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
