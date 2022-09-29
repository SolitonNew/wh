<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;
use App\Services\Admin\DaemonsService;
use App\Models\WebLogMem;
use App\Models\Property;
use App\Library\DaemonManager;

class DaemonsController extends Controller
{
    /**
     *
     * @var type
     */
    private $_service;

    /**
     *
     * @param DaemonsService $service
     */
    public function __construct(DaemonsService $service)
    {
        $this->_service = $service;
    }

    /**
     * Index route to display a list of daemons.
     *
     * @param string $id
     * @return type
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
            'stat' => $this->_service->isStarted($id),
            'daemons' => $this->_service->daemonsList(),
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
        $this->_service->daemonStart($id);

        return 'OK';
    }

    /**
     * This route stops the daemon by id.
     *
     * @param string $id
     * @return string
     */
    public function daemonStop(string $id)
    {
        $this->_service->daemonStop($id);

        return 'OK';
    }

    /**
     * This route restarts the daemon by id.
     *
     * @param string $id
     * @return string
     */
    public function daemonRestart(string $id)
    {
        $this->_service->daemonRestart($id);

        return 'OK';
    }

    /**
     * This route is for starting all daemons.
     *
     * @return string
     */
    public function daemonStartAll()
    {
        foreach ($this->_service->daemonsList() as $daemon) {
            if (!$daemon->stat) {
                $this->_service->daemonStart($daemon->id);
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
        foreach ($this->_service->daemonsList() as $daemon) {
            if ($daemon->stat) {
                $this->_service->daemonStop($daemon->id);
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
        return response()->json($this->_service->daemonsList());
    }
}
