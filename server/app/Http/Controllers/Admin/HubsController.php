<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HubsIndexRequest;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Services\Admin\HubsService;
use App\Models\ControllersModel;

class HubsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_hubsService;
    
    /**
     * 
     * @param HubsService $hubsService
     */
    public function __construct(HubsService $hubsService) 
    {
        $this->_hubsService = $hubsService;
    }
    
    /**
     * This is index route.
     * If the hub exists, to redirect to the device page.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(HubsIndexRequest $request, int $hubID = null) 
    {
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
        $item = ControllersModel::findOrCreate($id);
        
        return view('admin/hubs/hub-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * Route to create or update a hub property.
     * 
     * @param HubRequest $request
     * @param int $id
     * @return string
     */
    public function editPost(HubRequest $request, int $id) 
    {
        ControllersModel::storeFromRequest($request, $id);

        // Restart service daemons
        $this->_hubsService->restartServiceDaemons();

        return 'OK';
    }
    
    /**
     * Route to delete the hub by id.
     * 
     * @param int $id
     * @return type
     */
    public function delete(int $id) 
    {
        ControllersModel::deleteById($id);
        
        // Restart service daemons
        $this->_hubsService->restartServiceDaemons();
        
        return 'OK';
    }
    
    /**
     * This route scans child hosts.
     * Returns a view with scan dialog report.
     * 
     * @return type
     */
    public function hubsScan() 
    {
        $text = $this->_hubsService->hubsScan();
        
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
        list($text, $makeError) = $this->_hubsService->firmware();
        
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
        $this->_hubsService->firmwareStart();
        
        return 'OK';
    }
    
    /**
     * This route to query the firmware status now.
     * 
     * @return type
     */
    public function firmwareStatus() 
    {
        return $this->_hubsService->firmwareStatus();
    }
    
    /**
     * This route sends the din-daemon command to reboot all hubs. 
     * 
     * @return string
     */
    public function hubsReset() 
    {
        $this->_hubsService->hubsReset();
        
        return 'OK'; 
    }
}
