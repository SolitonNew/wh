<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;
use App\Services\Admin\DaemonsService;
use App\Models\WebLogMem;
use App\Models\Property;
use App\Library\DaemonManager;
use Illuminate\Support\Facades\Log;

class DaemonsController extends Controller
{
    /**
     * @var DaemonsService
     */
    private DaemonsService $service;

    /**
     * @param DaemonsService $service
     */
    public function __construct(DaemonsService $service)
    {
        $this->service = $service;
    }

    /**
     * Index route to display a list of daemons.
     *
     * @param DaemonManager $daemonManager
     * @param string|null $id
     * @return mixed
     * @throws \Exception
     */
    public function index(DaemonManager $daemonManager, string $id = null)
    {
        // Last view id  --------------------------
        if (!$id) {
            $id = Property::getLastViewID('DAEMON');
            if ($id && in_array($id, $daemonManager->daemons())) {
                return redirect(route('admin.jurnal-daemons', ['id' => $id]));
            }
            $id = null;
        }

        if (!$id) {
            $id = $daemonManager->daemons()[0];
            return redirect(route('admin.jurnal-daemons', ['id' => $id]));
        }

        Property::setLastViewID('DAEMON', $id);
        Property::setLastViewID('JURNAL_PAGE', 'daemons');
        // ----------------------------------------

        return view('admin.jurnal.daemons.daemons', [
            'id' => $id,
            'stat' => $this->service->isStarted($id),
            'daemons' => $this->service->daemonsList(),
        ]);
    }

    /**
     * This route returns the output of the daemons.
     *
     * @param string $id
     * @param int $lastID
     * @return string
     */
    public function data(string $id, int $lastID = -1)
    {
        $data = WebLogMem::getDaemonDataFromID($id, $lastID);

        return view('admin.jurnal.daemons.daemon-log', [
            'data' => $data,
        ]);
    }

    /**
     * This route starts the daemon by id.
     *
     * @param string $id
     * @return string
     */
    public function daemonStart(string $id)
    {
        return $this->service->daemonStart($id);
    }

    /**
     * This route stops the daemon by id.
     *
     * @param string $id
     * @return string
     */
    public function daemonStop(string $id)
    {
        return $this->service->daemonStop($id);
    }

    /**
     * This route restarts the daemon by id.
     *
     * @param string $id
     * @return string
     */
    public function daemonRestart(string $id)
    {
        return $this->service->daemonRestart($id);
    }

    /**
     * This route is for starting all daemons.
     *
     * @return string
     */
    public function daemonStartAll()
    {
        foreach ($this->service->daemonsList() as $daemon) {
            if (!$daemon->stat) {
                $this->service->daemonStart($daemon->id);
            }
        }

        return 'OK';
    }

    /**
     * This route is for stoping all daemons.
     *
     * @return string
     */
    public function daemonStopAll()
    {
        foreach ($this->service->daemonsList() as $daemon) {
            if ($daemon->stat) {
                $this->service->daemonStop($daemon->id);
            }
        }

        return 'OK';
    }

    /**
     * This route is for getting state of daemons.
     *
     * @return type
     */
    public function daemonsState()
    {
        return response()->json($this->service->daemonsList());
    }
}
