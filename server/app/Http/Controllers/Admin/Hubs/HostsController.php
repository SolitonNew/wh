<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Requests\Admin\HubsIndexRequest;
use App\Http\Controllers\Controller;
use App\Services\Admin\HostsService;
use App\Http\Requests\Admin\SoftHostRequest;
use App\Http\Requests\Admin\OwHostRequest;
use App\Models\SoftHost;
use App\Models\OwHost;

class HostsController extends Controller
{    
    /**
     *
     * @var type 
     */
    private $_service;
    
    /**
     * 
     * @param HostsService $service
     */
    public function __construct(HostsService $service)
    {
        $this->_service = $service;
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
        switch ($this->_service->getHostType($hubID)) {
            case 'software':
                return view('admin.hubs.hosts.soft.soft-hosts', [
                    'hubID' => $hubID,
                    'page' => 'hosts',
                    'data' => SoftHost::listForIndex($hubID),
                ]);
            case 'orangepi':
                return view('admin.hubs.hosts.orange.orange-hosts', [
                    'hubID' => $hubID,
                    'page' => 'hosts',
                    'data' => [],
                ]);
            case 'din':
                return view('admin.hubs.hosts.din.din-hosts', [
                    'hubID' => $hubID,
                    'page' => 'hosts',
                    'data' => OwHost::listForIndex($hubID),
                ]);
            case 'zigbeeone':
                return view('admin.hubs.hosts.zigbee.zigbee-hosts', [
                    'hubID' => $hubID,
                    'page' => 'hosts',
                    'data' => [],
                ]);
        }
        
        abort(404);
    }
    
    /**
     * Route to show software host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editSoftShow(int $hubID, int $id)
    {
        $item = SoftHost::findOrCreate($hubID, $id);
        
        return view('admin.hubs.hosts.soft.soft-host-edit', [
            'item' => $item,
        ]);
    }
    
    
    /**
     * Route to create or update software host properties.
     * 
     * @param SoftHostRequest $request
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editSoftPost(SoftHostRequest $request, int $hubID, int $id)
    {
        return SoftHost::storeFromRequest($request, $hubID, $id);
    }
    
    /**
     * Route to delete software host by id.
     * 
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function deleteSoft(int $hubID, int $id)
    {
        SoftHost::deleteById($id);
        
        return 'OK';
    }
    
    /**
     * Route to show orange pi host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editOrangeShow(int $hubID, int $id)
    {
        return 'DEMO';
    }
    
    
    /**
     * Route to create or update orange pi host properties.
     * 
     * @param SoftHostRequest $request
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editOrangePost(SoftHostRequest $request, int $hubID, int $id)
    {
        return 'DEMO'; 
    }
    
    /**
     * Route to delete orange pi host by id.
     * 
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function deleteOrange(int $hubID, int $id)
    {
        return 'DEMO';
    }
    
    /**
     * Route to show din host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editDinShow(int $hubID, int $id)
    {
        $item = OwHost::findOrCreate($hubID, $id);
        
        return view('admin.hubs.hosts.din.din-host-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * Route to create or update din host properties.
     * 
     * @param OwHostRequest $request
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editDinPost(OwHostRequest $request, int $hubID, int $id)
    {
        return OwHost::storeFromRequest($request, $hubID, $id);
    }
    
    /**
     * Route to delete Din host by id.
     * 
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function deleteDin(int $hubID, int $id)
    {
        OwHost::deleteById($id);
        
        return 'OK';
    }
    
    /**
     * Route to show Zigbee One host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editZigbeeShow(int $hubID, int $id)
    {
        return 'DEMO';
    }
    
    /**
     * Route to create or update Zigbee One host properties.
     * 
     * @param OwHostRequest $request
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editZigbeePost(OwHostRequest $request, int $hubID, int $id)
    {
        return 'DEMO';
    }
    
    /**
     * Route to delete Zigbee One host by id.
     * 
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function deleteZigbee(int $hubID, int $id)
    {        
        return 'DEMO';
    }
}
