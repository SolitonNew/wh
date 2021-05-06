<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use App\Http\Models\VariablesModel;
use App\Http\Models\PlanPartsModel;
use App\Http\Models\ControllersModel;
use Session;

class DevicesController extends Controller
{
    /**
     * This is an index route for displaying devices a list of the hub.
     * If the hub id does not exist, redirect to the owner route.
     * 
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null, $groupID = null) 
    {        
        if (!ControllersModel::find($hubID)) {
            return redirect(route('admin.hubs'));
        }
        
        if (!$groupID) {
            $groupID = Session::get('DEVICES_GROUP_ID');
        }
        
        if (!$groupID) {
            $groupID = 'none';
        }
        
        Session::put('HUB_INDEX_ID', $hubID);
        Session::put('DEVICES_GROUP_ID', $groupID);
        
        $data = VariablesModel::devicesList($hubID, $groupID);
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
            'data' => $data,
            'groupID' => $groupID,
        ]);
    }
    
    /**
     * Route to create or update device propertys.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editShow(int $hubID, int $id) 
    {
        $item = VariablesModel::findOrCreate($id, $hubID);
        $groupPath = PlanPartsModel::getPath($item->group_id, ' / ');

        return view('admin.hubs.devices.device-edit', [
            'item' => $item,
            'groupPath' => $groupPath,
        ]);
    }
    
    /**
     * Route to create or update device propertys.
     * 
     * @param DeviceRequest $request
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function editPost(DeviceRequest $request, int $hubID, int $id) 
    {
        VariablesModel::storeFromRequest($request, $hubID, $id);
        
        return 'OK';
    }
    
    /**
     * Route to delete the device by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        VariablesModel::deleteById($id);
        
        return 'OK';
    }
    
    /**
     * Route for requesting a list of hubs by host id.
     * 
     * @param int $hubID
     * @return type
     */
    public function hostList(int $hubID) 
    {
        $data = VariablesModel::hostList($hubID);
        
        return response()->json($data);
    }
    
    /**
     * Route for requesting a list of host channels by id.
     * 
     * @param string $typ [din, ow, variable]
     * @param int $hostID
     * @return type
     */
    public function hostChannelList(string $typ, int $hostID = null) 
    {
        $data = VariablesModel::hostChannelList($typ, $hostID);
        
        return response()->json($data);
    }
}
