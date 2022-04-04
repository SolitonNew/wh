<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Requests\Admin\HubsIndexRequest;
use App\Http\Controllers\Controller;
use App\Services\Admin\HostsService;

class HostsController extends Controller
{
    use SoftHostsTrait, DinHostsTrait;
    
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
     * @param HubsIndexRequest $request
     * @param int $hubID
     * @return type
     */
    public function index(HubsIndexRequest $request, int $hubID = null) 
    {        
        switch ($this->_hostsService->getHostType($hubID)) {
            case 'software':
                return $this->softIndex($hubID);
            case 'din':
                return $this->dinIndex($hubID);
        }
        
        abort(404);
    }
    
    /**
     * Route to show host properties.
     * 
     * @param int $nubId
     * @param int $id
     * @return type
     */
    public function editShow(int $hubID, int $id)
    {
        switch ($this->_hostsService->getHostType($hubID)) {
            case 'software':
                return $this->softEditShow($hubID, $id);
            case 'din':
                return $this->dinEditShow($hubID, $id);
        }
        
        abort(404);
    }
    
    /**
     * Route to create or update host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editPost(int $hubID, int $id)
    {
        switch ($this->_hostsService->getHostType($hubID)) {
            case 'software':
                return $this->softEditShow($hubID, $id);
            case 'din':
                return $this->dinEditShow($hubID, $id);
        }
        
        abort(404);
    }
    
    
    /**
     * Route to delete host by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $hubID, int $id) 
    {
        switch ($this->_hostsService->getHostType($hubID)) {
            case 'software':
                return $this->softDelete($id);
            case 'din':
                return $this->dinDelete($id);
        }
        
        abort(404);
    }
}
