<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;
use App\Services\Admin\DaemonsService;
use App\Http\Requests\Admin\DaemonsIndexRequest;
use App\Models\WebLog;

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
    public function index(DaemonsIndexRequest $request, string $id = null) 
    {        
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
        $data = WebLog::getDaemonDataFromID($id, $lastID);

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
}
