<?php

namespace App\Library\Daemons;

use App\Models\Device;
use App\Models\ExtApiHost;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class ExtApiDaemon extends BaseDaemon
{
    public const SIGNATURE = 'extapi-daemon';

    /**
     *
     */
    public const PROPERTY_NAME = 'EXT_API';

    /**
     * @var Collection|array
     */
    private Collection|array $providers = [];

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

        $this->printInitPrompt(Lang::get('admin/daemons/extapi-daemon.description'));

        // Base init
        if (!$this->initialization('extapi')) return ;

        try {
            while (1) {
                // ExtApi Host Providers Execute
                $this->executeHostProviders();

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

        $hosts = ExtApiHost::whereIn('hub_id', $ids)
            ->get();

        $list = [];
        foreach ($hosts as $host) {
            $driver = $host->driver();
            $this->providers[$host->id] = $driver;
            $list[] = $driver->title;
        }

        $this->printLine('PROVIDERS USED: ['.implode(', ', $list).']');
    }

    /**
     * @return void
     */
    private function executeHostProviders(): void
    {
        $now = floor(\Carbon\Carbon::now()->timestamp / 60);

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

        foreach ($this->providers as $id => $provider) {
            try {
                // Request
                if ($provider->canRequest()) {
                    $result = $provider->request();
                    $s = "[".parse_datetime(now())."] PROVIDER '".$provider->title."' HAS BEEN REQUESTED \n";
                    $this->printLine($s);
                    if ($result) {
                        $this->printLine($result);
                    }
                }

                // Update
                if ($provider->canUpdate()) {
                    $result = $provider->update();
                    if ($result) {
                        $s = "[".parse_datetime(now())."] PROVIDER '".$provider->title."' HAS BEEN UPDATED \n";
                        if ($result) {
                            $s .= $result;
                        }
                        $this->printLine($s);
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
     * @param Device $device
     * @return void
     */
    protected function deviceChangeValue(Device $device): void
    {

    }
}
