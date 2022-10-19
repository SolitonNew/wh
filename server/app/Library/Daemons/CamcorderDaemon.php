<?php

namespace App\Library\Daemons;

use App\Models\CamcorderHost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\Device;

class CamcorderDaemon extends BaseDaemon
{
    public const SIGNATURE = 'camcorder-daemon';

    public const PROPERTY_NAME = 'CAMCORDER';

    /**
     * @var array
     */
    private array $providers = [];

    /**
     * @var int|bool
     */
    private int|bool $prevExecuteHostProviderTime = false;

    /**
     * @return void
     */
    public function execute(): void
    {
        DB::select('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $this->printInitPrompt(Lang::get('admin/daemons/camcorder-daemon.description'));

        // Base init
        if (!$this->initialization('camcorder')) return ;

        try {
            while (1) {
                // ExtApi Host Providers Execute
                $this->executeHostProviders();

                // Check recording
                $this->checkRecording();

                // Check event log
                if (!$this->checkEvents()) break;

                usleep(100000);
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
    protected function initializationHosts(): void
    {
        $ids = $this->hubs
            ->pluck('id')
            ->toArray();

        $hosts = CamcorderHost::whereIn('hub_id', $ids)
            ->get();

        $list = [];
        foreach ($hosts as $host) {
            $driver = $host->driver();
            $this->providers[$host->id] = $driver;
            $list[] = $host->name.' ('.$driver->title.')';
        }

        $this->printLine('CAMCORDERS USED: ['.implode(', ', $list).']');

        // Camcorder folder
        $folder = base_path('storage/app/camcorder');
        if (!file_exists($folder)) {
            mkdir($folder);
        }

        // Thumbnails folder
        $folder = base_path('storage/app/camcorder/thumbnails');
        if (!file_exists($folder)) {
            mkdir($folder);
        }

        // Videos folder
        $folder = base_path('storage/app/camcorder/videos');
        if (!file_exists($folder)) {
            mkdir($folder);
        }
    }

    /**
     * @return void
     */
    private function executeHostProviders(): void
    {
        $now = floor(Carbon::now()->timestamp / 60);

        // Checking for execute after daemon restart.
        if ($this->prevExecuteHostProviderTime === false) {
            $this->prevExecuteHostProviderTime = $now;
            return ;
        }

        // Checking for execute at ever minutes.
        if ($now == $this->prevExecuteHostProviderTime) {
            return ;
        }

        // Storing the previous time value
        $this->prevExecuteHostProviderTime = $now;

        foreach ($this->providers as $provider) {
            try {
                // Request Thumbnail
                if ($provider->canThumbnailRequest()) {
                    $result = $provider->requestThumbnail();
                    $s = "[".parse_datetime(now())."] PROVIDER '".$provider->caption." (".$provider->title.")' HAS BEEN REQUEST THUMBNAIL \n";
                    $this->printLine($s);
                    if ($result) {
                        $this->printLine($result);
                    }
                }
            } catch (\Exception $ex) {
                $s = "[".parse_datetime(now())."] ERROR FOR '".$provider->title."'\n";
                $s .= $ex->getMessage();
                $this->printLine($s);
            }
        }
    }

    /**
     * @return void
     */
    private function checkRecording(): void
    {
        foreach ($this->devices as $device) {
            if ($device->value > 0 &&
                in_array($device->hub_id, $this->hubIds) &&
                $device->typ == 'camcorder' &&
                isset($this->providers[$device->host_id]))
            {
                $driver = $this->providers[$device->host_id];

                if (!$driver->checkRecording()) {
                    Device::setValue($device->id, 0);
                }
            }
        }
    }

    /**
     * @param Device $device
     * @return void
     */
    protected function deviceChangeValue(Device $device): void
    {
        if (in_array($device->hub_id, $this->hubIds) &&
            $device->typ == 'camcorder' &&
            isset($this->providers[$device->host_id]))
        {
            $driver = $this->providers[$device->host_id];

            switch ($device->channel) {
                case 'REC':
                    if ($device->value > 0) {
                        $result = $driver->startRecording($device->lastDeviceChangesID);
                    } else {
                        $result = $driver->stopRecording();
                    }
                    if ($result) {
                        $this->printLine('['.parse_datetime(now()).'] '.$result);
                    }
                    break;
                default:
                    break;
            }
        }
    }
}
