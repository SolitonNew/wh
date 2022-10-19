<?php

namespace App\Library;

use Illuminate\Support\Facades\Log;

/**
 * DemonManager Process Manager.
 *
 * @author soliton
 */
class DaemonManager
{
    /**
     * @var array|\Laravel\Lumen\Application|mixed
     */
    protected array $daemons = [];

    /**
     *
     */
    public function __construct()
    {
        foreach (config('daemons.list') as $daemonClass) {
            $this->daemons[] = $daemonClass::SIGNATURE;
        }
    }

    /**
     * @return array
     */
    public function daemons(): array
    {
        return $this->daemons;
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getDaemonClass(string $id)
    {
        foreach (config('daemons.list') as $daemonClass) {
            if ($daemonClass::SIGNATURE == $id) {
                return $daemonClass;
            }
        }
        return null;
    }

    /**
     * Checks the correctness of the ID by checking the list of registered.
     *
     * @param string $id
     * @return bool
     */
    public function exists(string $id): bool
    {
        return in_array($id, $this->daemons);
    }

    /**
     * Checks if a process is running on the system.
     *
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function isStarted(string $id): bool
    {
        if ($this->exists($id)) {
            return count($this->findDaemonPID($id)) > 0;
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }

    /**
     * Starts a process.
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function start(string $id): void
    {
        if ($this->exists($id)) {
            exec('php '.base_path().'/artisan daemon:run '.$id.'>/dev/null &');
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }

    /**
     * Stops a process.
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function stop(string $id): void
    {
        if ($this->exists($id)) {
            foreach ($this->findDaemonPID($id) as $pid) {
                exec('kill -9 '.$pid);
            }
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }

    /**
     * Restarts the process. If the process was stopped, it starts it.
     *
     * @param string $id
     * @return void
     * @throws \Exception
     */
    public function restart(string $id): void
    {
        if ($this->exists($id)) {
            if ($this->isStarted($id)) {
                $this->stop($id);
            }
            $this->start($id);
        } else {
            throw new \Exception('Non-existent process ID');
        }
    }

    /**
     * Executes a query to the OS and returns the search result for daemons
     * in the form of an array.
     *
     * @param string $id
     * @return array
     */
    public function findDaemonPID(string $id): array
    {
        $pids = [];
        exec("ps axw | grep $id | grep -v grep | grep -v 'sh -c '", $outs);
        foreach ($outs as $out) {
            $a = explode(' ', trim($out));
            if (count($a)) {
                $pids[] = $a[0];
            }
        }
        return $pids;
    }
}
