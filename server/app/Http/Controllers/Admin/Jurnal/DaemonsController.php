<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;
use App\Http\Services\Admin\DaemonsService;
use App\Http\Requests\Admin\DaemonsIndexRequest;
use App\Models\WebLogsModel;

class DaemonsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_daemonsService;
    
    /**
     * 
     * @param DaemonsService $daemonsService
     */
    public function __construct(DaemonsService $daemonsService) 
    {
        $this->_daemonsService = $daemonsService;
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
            'stat' => $this->_daemonsService->isStarted($id),
            'daemons' => $this->_daemonsService->daemonsList(),
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
        $data = WebLogsModel::getDaemonDataFromID($id, $lastID);

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
        $this->_daemonsService->daemonStart($id);
        
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
        $this->_daemonsService->daemonStop($id);
        
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
        $this->_daemonsService->daemonRestart($id);
        
        return 'OK';
    }    
}
