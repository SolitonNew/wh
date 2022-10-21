<?php

namespace App\Services\Admin;

use App\Library\Daemons\DinDaemon;
use App\Library\Daemons\OrangePiDaemon;
use App\Library\Daemons\PyhomeDaemon;
use App\Library\Daemons\ZigbeeoneDaemon;
use App\Library\Firmware\Pyhome;
use App\Library\Firmware\ZigbeeOne;
use App\Models\Hub;
use App\Models\Device;
use App\Models\OwHost;
use App\Models\I2cHost;
use App\Models\ExtApiHost;
use App\Models\CamcorderHost;
use App\Models\Property;
use App\Library\DaemonManager;
use App\Library\Firmware\Din;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HubsService
{
    /**
     * @return string
     */
    public function dinHubsScan(): string
    {
        DinDaemon::setCommand('OW SEARCH');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = DinDaemon::getCommandInfo();
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
    public function pyhomeHubsScan(): string
    {
        PyhomeDaemon::setCommand('OW SEARCH');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = PyhomeDaemon::getCommandInfo();
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
    public function zigbeeoneHubsScan(): string
    {
        ZigbeeoneDaemon::setCommand('OW SEARCH');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = ZigbeeoneDaemon::getCommandInfo();
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
        OrangePiDaemon::setCommand('SCAN');
        usleep(500000);
        $i = 0;
        while ($i++ < 50) { // 5 sec
            usleep(100000);
            $text = OrangePiDaemon::getCommandInfo();
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
                $this->generateOwDevsByHub($hubID);
                break;
            case 'pyhome':
                $this->generatePyhomeDevsByHub($hubID);
                $this->generateOwDevsByHub($hubID);
                break;
            case 'orangepi':
                $this->generateOrangePiDevsByHub($hubID);
                $this->generateOwDevsByHub($hubID);
                $this->generateI2cDevsByHub($hubID);
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
            0 => ['MEM_TOTAL', 'MEM_FREE'],
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
    private function generateOwDevsByHub(int $hubID): void
    {
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
    private function generateI2cDevsByHub(int $hubID): void
    {
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
    private function generateDinDevsByHub(int $hubID): void
    {
        $din_channels = config('din.mmcu_list.'.DinDaemon::getSettings('MMCU').'.channels');
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
     * @param string $typ
     * @return string
     */
    public function firmwareMake(string $typ): string
    {
        try {
            switch ($typ) {
                case 'din':
                    list($text, $makeError) = $this->firmwareDinMake();
                    if ($makeError) {
                        return $text;
                    }
                    return 'OK';
                case 'pyhome':
                    $pyhome = new Pyhome();
                    $pyhome->generateConfig();
                    return 'OK';
                case 'zigbeeone':
                    $zigbeeone = new ZigbeeOne();
                    $zigbeeone->generateConfig();
                    return 'OK';
            }
            return 'Type not found';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * @return array
     */
    public function firmwareDinMake(): array
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
    public function configWizardTransmit(): void
    {
        DinDaemon::setCommand('FIRMWARE');
        DinDaemon::setCommandInfo('', true);

        PyhomeDaemon::setCommand('CONFIG UPDATE');
        PyhomeDaemon::setCommandInfo('', true);

        ZigbeeoneDaemon::setCommand('CONFIG UPDATE');
        ZigbeeoneDaemon::setCommandInfo('', true);
    }

    /**
     * @param array $ids
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function configWizardStatus(array $ids)
    {
        $hubs = Hub::whereIn('id', $ids)->get();

        $result = [];
        foreach ($hubs as $hub) {
            $status = '';
            $percent = 0;
            $details = '';

            switch ($hub->typ) {
                case 'din':
                    list($status, $percent, $details) = $this->configWizardStatusDin($hub->id);
                    break;
                case 'pyhome':
                    list($status, $percent, $details) = $this->configWizardStatusPyhome($hub->id);
                    break;
                case 'zigbeeone':
                    list($status, $percent, $details) = $this->configWizardStatusZigbeeone($hub->id);
                    break;
            }

            $result[] = (object)[
                'id' => $hub->id,
                'status' => $status ?: 'PENDING',
                'percent' => $percent,
                'details' => $details,
            ];
        }

        return response()->json($result);
    }

    /**
     * @param int $id
     * @return array
     */
    private function configWizardStatusDin(int $id): array
    {
        $daemonManager = new DaemonManager();

        $status = '';
        $percent = 0;
        $details = '';

        try {
            if (!$daemonManager->isStarted('din-daemon')) {
                throw new \Exception('NOT POSSIBLE');
            }

            $info = DinDaemon::getCommandInfo();
            $a = [];
            foreach (explode(';', $info) as $s) {
                $c = explode(':', $s);
                if (count($c) == 2 && $c[0] == $id) {
                    if ($c[1] == 'BAD') {
                        $status = 'ERROR';
                        $details = 'Bad firmware size';
                    } else
                    if ($c[1] == 'COMPLETE') {
                        $status = 'COMPLETE';
                        $percent = 100;
                    } else {
                        $status = 'IN PROGRESS';
                        $percent = $c[1];
                    }
                    break;
                }
            }
        } catch (\Exception $ex) {
            $status = 'ERROR';
            $details = $ex->getMessage();
        }

        return [$status, $percent, $details];
    }

    /**
     * @param int $id
     * @return array
     */
    private function configWizardStatusPyhome(int $id): array
    {
        $daemonManager = new DaemonManager();

        $status = '';
        $percent = 0;
        $details = '';

        try {
            if (!$daemonManager->isStarted('pyhome-daemon')) {
                throw new \Exception('NOT POSSIBLE');
            }

            $info = PyhomeDaemon::getCommandInfo();
            $a = [];
            foreach (explode(';', $info) as $s) {
                $c = explode(':', $s);
                if (count($c) == 2 && $c[0] == $id) {
                    if ($c[1] == 'BAD') {
                        $status = 'ERROR';
                        $details = 'Bad controller response';
                    } else
                        if ($c[1] == 'COMPLETE') {
                            $status = 'COMPLETE';
                            $percent = 100;
                        } else {
                            $status = 'IN PROGRESS';
                            $percent = $c[1];
                        }
                    break;
                }
            }
        } catch (\Exception $ex) {
            $status = 'ERROR';
            $details = $ex->getMessage();
        }

        return [$status, $percent, $details];
    }

    /**
     * @param int $id
     * @return array
     */
    private function configWizardStatusZigbeeone(int $id): array
    {
        $daemonManager = new DaemonManager();

        $status = '';
        $percent = 0;
        $details = '';

        try {
            if (!$daemonManager->isStarted('zigbeeone-daemon')) {
                throw new \Exception('NOT POSSIBLE');
            }

            $info = ZigbeeoneDaemon::getCommandInfo();
            $a = [];
            foreach (explode(';', $info) as $s) {
                $c = explode(':', $s);
                if (count($c) == 2 && $c[0] == $id) {
                    if ($c[1] == 'BAD') {
                        $status = 'ERROR';
                        $details = 'Bad controller response';
                    } else
                        if ($c[1] == 'COMPLETE') {
                            $status = 'COMPLETE';
                            $percent = 100;
                        } else {
                            $status = 'IN PROGRESS';
                            $percent = $c[1];
                        }
                    break;
                }
            }
        } catch (\Exception $ex) {
            $status = 'ERROR';
            $details = $ex->getMessage();
        }

        return [$status, $percent, $details];
    }

    /**
     * @return string
     */
    public function hubsReset(): string
    {
        try {
            DinDaemon::setCommand('RESET');
            PyhomeDaemon::setCommand('RESET');
            ZigbeeoneDaemon::setCommand('RESET');
            OrangePiDaemon::setCommand('RESET');
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR';
        }
    }

    /**
     * @param int $hubID
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getFirmwareFullPack(int $hubID)
    {
        $hub = Hub::find($hubID);
        switch ($hub->typ) {
            case 'pyhome':
                $fileName = storage_path('app/'.Carbon::now()->format('Y-m-d H-i-s ').$hub->name).'.zip';
                (new Pyhome())->makeFullFirmwareZipByRom($hub->rom, $fileName);
                return response()->download($fileName);
        }
        return 'ERROR';
    }
}
