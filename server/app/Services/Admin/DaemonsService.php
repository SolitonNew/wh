<?php

namespace App\Services\Admin;

use App\Library\DaemonManager;
use App\Models\Property;

class DaemonsService
{
    private $_daemonManager;

    public function __construct()
    {
        $this->_daemonManager = new DaemonManager();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function daemonsList(): array
    {
        $daemons = [];
        foreach ($this->_daemonManager->daemons() as $dem) {
            $stat = $this->_daemonManager->isStarted($dem);
            $daemons[] = (object)[
                'id' => $dem,
                'stat' => $stat,
                'idName' => $this->makeDaemonName($dem),
            ];
        }

        return $daemons;
    }

    /**
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function isStarted(string $id): bool
    {
        return $this->_daemonManager->isStarted($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function existsDaemon(string $id): bool
    {
        return $this->_daemonManager->exists($id);
    }

    /**
     * @param string $id
     * @return void
     */
    public function daemonStart(string $id): void
    {
        try {
            Property::setAsRunningDaemon($id);
            $this->_daemonManager->start($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * @param string $id
     * @return void
     */
    public function daemonStop(string $id): void
    {
        try {
            Property::setAsStoppedDaemon($id);
            $this->_daemonManager->stop($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * @param string $id
     * @return void
     */
    public function daemonRestart(string $id): void
    {
        try {
            Property::setAsRunningDaemon($id);
            $this->_daemonManager->restart($id);
            usleep(250000);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * @param string $id
     * @return string
     */
    public function makeDaemonName(string $id): string
    {
        $a = explode(':', $id);
        return $a[0];
    }
}
