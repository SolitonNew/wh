<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Requests\HubsIndexRequest;
use App\Http\Controllers\Controller;
use App\Http\Services\HostsService;

class HostsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_hostsService;
    
    /**
     * 
     * @param HostsService $hostService
     */
    public function __construct(HostsService $hostService) 
    {
        $this->_hostsService = $hostService;
    }
    
    /**
     * This is an index route for displaying a list of host.
     * If the hub id does not exists, redirect to the owner route.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(HubsIndexRequest $request, int $hubID = null) 
    {        
        $data = $this->_hostsService->getIndexList($hubID);
        
        return view('admin.hubs.hosts.hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => $data,
        ]);
    }
    
    /**
     * Route to show host propertys.
     * 
     * @param int $nubId
     * @param int $id
     * @return type
     */
    public function editShow(int $nubId, int $id)
    {
        $item = $this->_hostsService->getOneHost($id);
        
        return view('admin.hubs.hosts.host-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * Route to delete host by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        $this->_hostsService->delOneHost($id);
        
        return 'OK';
    }
}
