<?php

namespace App\Services\Admin;

use App\Models\Hub;
use App\Models\Device;
use App\Models\OwHost;
use App\Models\I2cHost;
use App\Models\ExtApiHost;
use App\Models\CamcorderHost;
use App\Models\Property;
use App\Library\DaemonManager;
use App\Library\Firmware\Din;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HubsService
{
    /**
     * @return string
     */
    public function dinHubsScan(): string
    {
        Property::setDinCommand('OW SEARCH');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = Property::getDinCommandInfo();
            if ($t = strpos($text, 'END_OW_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }
        return $text;
    }

    /**
     * @return string
     */
    public function orangepiHubScan(): string
    {
        Property::setOrangePiCommand('SCAN');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = Property::getOrangePiCommandInfo();
            if ($t = strpos($text, 'END_SCAN')) {
                $text = substr($text, 0, $t);
                break;
            }
        }
        return $text;
    }

    /**
     * @param int $hubID
     * @return void
     */
    public function generateDevsByHub(int $hubID): void
    {
        $hub = Hub::find($hubID);

        switch ($hub->typ) {
            case 'din':
                $this->generateDinDevsByHub($hubID);
                break;
            case 'pyhome':
                $this->generatePyhomeDevsByHub($hubID);
                break;
            case 'orangepi':
                $this->generateOrangePiDevsByHub($hubID);
                break;
            case 'camcorder':
                $this->generateCamcorderDevsByHub($hubID);
                break;
            case 'extapi':
                $this->generateExtApiDevsByHub($hubID);
                break;
        }
    }

    /**
     * @param string $channel
     * @param int $default
     * @return int
     */
    private function decodeChannelTyp(string $channel, int $default = 0): int
    {
        $channelControl = [
            1 => [
                'R1', 'R2', 'R3', 'R4',
                'X1', 'X2', 'X3', 'X4',
                'X5', 'X6', 'X7', 'X8',
                'X9', 'X10', 'X11', 'X12',
                'Y1', 'Y2', 'Y3', 'Y4',
                'Y5', 'Y6', 'Y7', 'Y8',
            ],      // Light
            2 => ['LEFT', 'RIGHT', 'LEFT_LONG', 'RIGHT_LONG'], // Switch
            //3 => [],                          // Socket
            4 => ['T', 'TEMP', 'PROC_TEMP'],    // Termometr
            //5 => [],                          // Termostat
            6 => ['REC'],                       // Camcorder
            7 => ['F1', 'F2', 'F3', 'F4'],      // Venting
            8 => ['P1', 'P2', 'P3', 'P4', 'MOTION'],  // Motion sensor
            //9 => [],                          // Leakage sensor
            10 => ['H'],                        // Humidity
            11 => ['CO'],                       // Gas sensor
            //12 => [],                         // Door sensor
            13 => ['P'],                        // Atm. pressure
            14 => ['AMP'],                      // Currency sensor
            15 => ['G', 'WS'],                  // Speed
            16 => ['WD'],                       // Direction
            17 => ['V', ''],                    // Distance
            18 => ['H', 'CC'],                  // Percents
            19 => ['MP'],                       // Height
        ];

        foreach ($channelControl as $key => $val) {
            if (in_array($channel, $val)) {
                return $key;
            }
        }

        return $default;
    }

    /**
     * @param int $hubID
     * @return void
     */
    private function generateDinDevsByHub(int $hubID): void
    {
        $din_channels = config('din.'.Property::getDinSettings()->mmcu.'.channels');
        $devs = DB::select('select hub_id, channel from core_devices where hub_id = '.$hubID.' and typ = "din"');

        try {
            foreach ($din_channels as $chan) {
                $find = false;
                foreach ($devs as $dev) {
                    if ($dev->hub_id == $hubID && $dev->channel == $chan) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $item = new Device();
                    $item->hub_id = $hubID;
                    $item->typ = 'din';
                    $item->name = 'temp for din';
                    $item->host_id = null;
                    $item->channel = $chan;
                    $item->app_control = $this->decodeChannelTyp($chan, 1);
                    $item->save(['withoutevents']);
                    $item->name = 'din_'.$item->id.'_'.$chan;
                    $item->save();
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }

        // Generation of devices for network hubs
        $hosts = OwHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('ow')->get();
        try {
            foreach ($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach ($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'ow';
                        $item->name = 'temp for ow';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'ow_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
    }

    /**
     * @param int $hubID
     * @return void
     */
    private function generatePyhomeDevsByHub(int $hubID): void
    {
        $pyhome_channels = config('pyhome.channels');
        $devs = DB::select('select hub_id, channel from core_devices where hub_id = '.$hubID.' and typ = "pyhome"');

        try {
            foreach ($pyhome_channels as $chan) {
                $find = false;
                foreach ($devs as $dev) {
                    if ($dev->hub_id == $hubID && $dev->channel == $chan) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $item = new Device();
                    $item->hub_id = $hubID;
                    $item->typ = 'pyhome';
                    $item->name = 'temp for pyhome';
                    $item->host_id = null;
                    $item->channel = $chan;
                    $item->app_control = $this->decodeChannelTyp($chan, 1);
                    $item->save(['withoutevents']);
                    $item->name = 'pyhome_'.$item->id.'_'.$chan;
                    $item->save();
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }

        // Generation of devices for network hubs
        $hosts = OwHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('ow')->get();
        try {
            foreach ($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach ($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'ow';
                        $item->name = 'temp for ow';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'ow_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
    }

    /**
     * @param int $hubID
     * @return void
     */
    private function generateOrangePiDevsByHub(int $hubID): void
    {
        $channels = config('orangepi.channels');
        $devs = DB::select('select hub_id, channel from core_devices where hub_id = '.$hubID.' and typ = "orangepi"');

        try {
            foreach ($channels as $chan => $num) {
                $find = false;
                foreach ($devs as $dev) {
                    if ($dev->hub_id == $hubID && $dev->channel == $chan) {
                        $find = true;
                        break;
                    }
                }
                if (!$find) {
                    $item = new Device();
                    $item->hub_id = $hubID;
                    $item->typ = 'orangepi';
                    $item->name = 'temp for din';
                    $item->host_id = null;
                    $item->channel = $chan;
                    $item->app_control = $this->decodeChannelTyp($chan, 1);
                    $item->save(['withoutevents']);
                    $item->name = 'orangepi_'.$item->id.'_'.$chan;
                    $item->save();
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }

        // Generation of devices for network hubs
        // Generation of devices for network hubs
        $hosts = I2cHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('i2c')->get();
        try {
            foreach ($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach ($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'i2c';
                        $item->name = 'temp for i2c';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'i2c_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
    }

    /**
     * @param int $hubID
     * @return void
     */
    private function generateExtApiDevsByHub(int $hubID): void
    {
        $hosts = ExtApiHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('extapi')->get();

        try {
            foreach ($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach ($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'extapi';
                        $item->name = 'temp for extapi';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'extapi_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
    }

    /**
     * @param int $hubID
     * @return void
     */
    private function generateCamcorderDevsByHub(int $hubID): void
    {
        $hosts = CamcorderHost::whereHubId($hubID)->get();
        $devs = Device::whereTyp('camcorder')->get();

        try {
            foreach ($hosts as $host) {
                foreach ($host->channelsOfType() as $chan) {
                    $find = false;
                    foreach ($devs as $dev) {
                        if ($dev->host_id == $host->id && $dev->channel && $dev->channel == $chan) {
                            $find = true;
                            break;
                        }
                    }

                    if (!$find) {
                        $item = new Device();
                        $item->hub_id = $host->hub_id;
                        $item->typ = 'camcorder';
                        $item->name = 'temp for camcorder';
                        $item->host_id = $host->id;
                        $item->channel = $chan;
                        $item->app_control = $this->decodeChannelTyp($chan);
                        $item->save(['withoutevents']);
                        $item->name = 'cam_'.$item->id.'_'.$chan;
                        $item->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            Log::info($ex);
            return ;
        }
    }

    /**
     * This method creted devices entries on each channel if the channel
     * does not exists.
     *
     * @return void
     */
    public function generateDevs(): void
    {
        foreach (Hub::get() as $hub) {
            $this->generateDevsByHub($hub->id);
        }
    }

    /**
     * This is the service daemons reboot method.
     *
     * @return string|null
     */
    public function restartServiceDaemons(): string|null
    {
        $daemons = [
            'din-daemon',
            'extapi-daemon',
            'orangepi-daemon',
            'camcorder-daemon',
        ];

        $daemonManager = new DaemonManager();
        try {
            foreach ($daemons as $daemon) {
                Property::setAsRunningDaemon($daemon);
                $daemonManager->restart($daemon);
            }
            return 'OK';
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * @return array
     */
    public function firmware(): array
    {
        $makeError = false;
        $text = '';
        try {
            $firmware = new Din();
            $firmware->generateConfig();

            $outs = [];
            if ($firmware->make($outs)) {
                $text = implode("\n", $outs);
            } else {
                $makeError = true;
                $text = implode("\n", $outs);
            }
        } catch (\Exception $ex) {
            $makeError = true;
            $text = $ex->getMessage();
        }

        return [
            $text,
            $makeError
        ];
    }

    /**
     * @return void
     */
    public function firmwareStart(): void
    {
        Property::setDinCommand('FIRMWARE');
        Property::setDinCommandInfo('', true);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function firmwareStatus()
    {
        $daemonManager = new DaemonManager();

        try {
            if (!$daemonManager->isStarted('din-daemon')) {
                return response()->json([
                    'firmware' => 'NOTPOSSIBLE',
                ]);
            }

            $info = Property::getDinCommandInfo();
            if ($info == 'COMPLETE') {
                return response()->json([
                    'firmware' => 'COMPLETE',
                ]);
            } else
            if (strpos($info, 'ERROR') !== false) {
                return response()->json([
                    'error' => $info,
                ]);
            } else {
                $a = explode(';', $info);
                if (count($a) < 2) {
                    $a = ['', 0];
                }
                return response()->json([
                    'controller' => $a[0],
                    'percent' => $a[1],
                ]);
            }
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * @return void
     */
    public function hubsReset(): void
    {
        try {
            Property::setDinCommand('RESET');
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }
}
