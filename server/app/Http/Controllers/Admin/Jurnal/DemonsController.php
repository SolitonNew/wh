<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Requests\DemonsIndexRequest;
use App\Http\Controllers\Controller;
use App\Http\Services\DemonsService;
use App\Http\Models\WebLogsModel;

class DemonsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_demonsService;
    
    /**
     * 
     * @param DemonsService $demonsService
     */
    public function __construct(DemonsService $demonsService) 
    {
        $this->_demonsService = $demonsService;
    }
    
    /**
     * Index route to display a list of daemons.
     * 
     * @param string $id
     * @return type
     */
    public function index(DemonsIndexRequest $request, string $id = null) 
    {        
        return view('admin.jurnal.demons.demons', [
            'id' => $id,
            'stat' => $this->_demonsService->isStarted($id),
            'demons' => $this->_demonsService->demonsList(),
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
        $data = WebLogsModel::getDemonDataFromID($id, $lastID);

        return view('admin.jurnal.demons.demon-log', [
            'data' => $data,
        ]);
    }

    /**
     * This route starts the daemon by id.
     * 
     * @param string $id
     * @return string
     */
    public function demonStart(string $id) 
    {
        $this->_demonsService->demonStart($id);
        
        return 'OK';
    }

    /**
     * This route stops the daemon by id.
     * 
     * @param string $id
     * @return string
     */
    public function demonStop(string $id) 
    {
        $this->_demonsService->demonStop($id);
        
        return 'OK';
    }

    /**
     * This route restarts the daemon by id.
     * 
     * @param string $id
     * @return string
     */
    public function demonRestart(string $id) 
    {
        $this->_demonsService->demonRestart($id);
        
        return 'OK';
    }    
}
