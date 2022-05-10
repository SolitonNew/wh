<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Controllers\Controller;
use App\Services\Admin\HostsService;
use Illuminate\Http\Request;
use App\Models\ExtApiHost;
use App\Models\OwHost;
use App\Models\I2cHost;
use App\Models\Property;
use App\Models\Hub;

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
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) 
    {        
        // Last view id  --------------------------
        if (!$hubID) {
            $hubID = Property::getLastViewID('HUB');
            if ($hubID) {
                return redirect(route('admin.hub-hosts', ['hubID' => $hubID]));
            } else {
                $hubID = null;
            }
        }
        
        if (!$hubID) {
            $item = Hub::orderBy('name', 'asc')->first();
            if ($item) {
                return redirect(route('admin.hub-hosts', ['hubID' => $item->id]));
            }
        }
        
        Property::setLastViewID('HUB', $hubID);
        // ----------------------------------------
        
        
        switch ($this->_service->getHostType($hubID)) {
            case 'extapi':
                return view('admin.hubs.hosts.extapi.extapi-hosts', [
                    'hubID' => $hubID,
                    'page' => 'hosts',
                    'data' => ExtApiHost::listForIndex($hubID),
                ]);
            case 'orangepi':
                return view('admin.hubs.hosts.orange.orange-hosts', [
                    'hubID' => $hubID,
                    'page' => 'hosts',
                    'data' => I2cHost::listForIndex($hubID),
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
            default:
                return redirect(route('admin.hubs'));
        }
        
        abort(404);
    }
    
    /**
     * Route to show extapi host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editExtApiShow(int $hubID, int $id)
    {
        $item = ExtApiHost::findOrCreate($hubID, $id);
        
        return view('admin.hubs.hosts.extapi.extapi-host-edit', [
            'item' => $item,
        ]);
    }
    
    
    /**
     * Route to create or update extapi host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editExtApiPost(Request $request, int $hubID, int $id)
    {
        return ExtApiHost::storeFromRequest($request, $hubID, $id);
    }
    
    /**
     * Route to delete extapi host by id.
     * 
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function deleteSoft(int $hubID, int $id)
    {
        return ExtApiHost::deleteById($id);
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
        $item = I2cHost::findOrCreate($hubID, $id);
        
        return view('admin.hubs.hosts.orange.orange-host-edit', [
            'item' => $item,
        ]);
    }
    
    
    /**
     * Route to create or update orange pi host properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editOrangePost(Request $request, int $hubID, int $id)
    {
        return I2cHost::storeFromRequest($request, $hubID, $id);
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
        return I2cHost::deleteById($id);
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
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editDinPost(Request $request, int $hubID, int $id)
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
        return OwHost::deleteById($id);
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
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editZigbeePost(Request $request, int $hubID, int $id)
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
