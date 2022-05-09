<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\HubsService;
use App\Models\Hub;
use App\Models\Property;
use Log;

class HubsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_service;
    
    /**
     * 
     * @param HubsService $hubsService
     */
    public function __construct(HubsService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * This is index route.
     * If the hub exists, to redirect to the device page.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null) 
    {
        // Last view id  --------------------------
        if (!$hubID) {
            $hubID = Property::getLastViewID('HUB');
            if ($hubID && Hub::find($hubID)) {
                return redirect(route('admin.hub-hosts', ['hubID' => $hubID]));
            }
            $hubID = null;
        }
        
        if (!$hubID) {
            $item = Hub::orderBy('name', 'asc')->first();
            if ($item) {
                return redirect(route('admin.hub-hosts', ['hubID' => $item->id]));
            }
        }
        
        Property::setLastViewID('HUB', $hubID);
        // ----------------------------------------
        
        return view('admin/hubs/hubs', [
            'hubID' => $hubID,
        ]);
    }
    
    /**
     *  Route to create or update a hub property.
     * 
     * @param int $id
     * @return type
     */
    public function editShow(int $id)
    {
        $item = Hub::findOrCreate($id);
        
        return view('admin/hubs/hub-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * Route to create or update a hub property.
     * 
     * @param int $id
     * @return string
     */
    public function editPost(Request $request, int $id) 
    {
        return Hub::storeFromRequest($request, $id);
    }
    
    /**
     * Route to delete the hub by id.
     * 
     * @param int $id
     * @return type
     */
    public function delete(int $id) 
    {
        return Hub::deleteById($id);
    }
    
    /**
     * This route scans child hosts.
     * Returns a view with scan dialog report.
     * 
     * @return type
     */
    public function hubsScan() 
    {
        $text = $this->_service->hubsScan();
        
        return view('admin.hubs.hubs-scan', [
            'data' => $text,
        ]);
    }
    
    /**
     * This route builds the firmware and returns a build report view 
     * containing the update controls.
     * 
     * @return type
     */
    public function firmware()
    {
        list($text, $makeError) = $this->_service->firmware();
        
        return view('admin.hubs.firmware', [
            'data' => $text,
            'makeError' => $makeError,
        ]);
    }
    
    /**
     * This route sends the din-daemon command to start uploading firmware 
     * to the controllers.
     * 
     * @return string
     */
    public function firmwareStart() 
    {
        $this->_service->firmwareStart();
        
        return 'OK';
    }
    
    /**
     * This route to query the firmware status now.
     * 
     * @return type
     */
    public function firmwareStatus() 
    {
        return $this->_service->firmwareStatus();
    }
    
    /**
     * This route sends the din-daemon command to reboot all hubs. 
     * 
     * @return string
     */
    public function hubsReset() 
    {
        $this->_service->hubsReset();
        
        return 'OK'; 
    }
    
    /**
     * This route creates devices for all hosts, if that devices are not exists.
     * 
     * @param int $hubID
     * @return string
     */
    public function addDevicesForAllHosts(int $hubID)
    {
        $this->_service->_generateDevsByHub($hubID);
        
        return 'OK';
    }
}
