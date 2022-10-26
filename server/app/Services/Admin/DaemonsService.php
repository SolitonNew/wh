<?php

namespace App\Services\Admin;

use App\Library\DaemonManager;
use App\Models\Property;

class DaemonsService
{
    private DaemonManager $daemonManager;

    public function __construct()
    {
        $this->daemonManager = new DaemonManager();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function daemonsList(): array
    {
        $daemons = [];
        $started = $this->daemonManager->findAllStartedDaemons();
        foreach (config('daemons.list') as $daemonClass) {
            if ($daemonClass::canRun()) {
                $stat = in_array($daemonClass::SIGNATURE, $started);
                $daemons[] = (object)[
                    'id' => $daemonClass::SIGNATURE,
                    'stat' => $stat,
                    'idName' => $this->makeDaemonName($daemonClass::SIGNATURE),
                    'workingState' => $daemonClass::getWorkingState(),
                ];
            }
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
        return $this->daemonManager->isStarted($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function existsDaemon(string $id): bool
    {
        return $this->daemonManager->exists($id);
    }

    /**
     * @param string $id
     * @return string
     */
    public function daemonStart(string $id): string
    {
        try {
            $daemon = $this->daemonManager->getDaemonClass($id);
            $daemon::setWorkingState(true);
            $this->daemonManager->start($id);
            usleep(250000);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR: '.$ex->getMessage();
        }
    }

    /**
     * @param string $id
     * @return string
     */
    public function daemonStop(string $id): string
    {
        try {
            $daemon = $this->daemonManager->getDaemonClass($id);
            $daemon::setWorkingState(false);
            $this->daemonManager->stop($id);
            usleep(250000);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR: '.$ex->getMessage();
        }
    }

    /**
     * @param string $id
     * @return string
     */
    public function daemonRestart(string $id): string
    {
        try {
            $daemon = $this->daemonManager->getDaemonClass($id);
            $daemon::setWorkingState(true);
            $this->daemonManager->restart($id);
            usleep(250000);
            return 'OK';
        } catch (\Exception $ex) {
            return 'ERROR: '.$ex->getMessage();
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
