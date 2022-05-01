<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HubsIndexRequest;
use App\Http\Requests\Admin\DeviceRequest;
use App\Services\Admin\DevicesService;
use App\Models\Device;
use App\Models\Room;
use App\Models\Hub;
use App\Models\OwHost;
use App\Models\SoftHost;
use App\Models\Property;

class DevicesController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_service;
    
    /**
     * 
     * @param DevicesService $service
     */
    public function __construct(DevicesService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * This is an index route for displaying devices a list of the hub.
     * If the hub id does not exist, redirect to the owner route.
     * 
     * @param HubsIndexRequest $request
     * @param int $hubID
     * @param type $groupID
     * @return type
     */
    public function index(HubsIndexRequest $request, int $hubID = null, $groupID = null) 
    {                
        $groupID = $this->_service->prepareRoomFilter($groupID);

        $data = Device::devicesList($hubID, $groupID);
        
        return view('admin.hubs.devices.devices', [
            'hubID' => $hubID,
            'page' => 'devices',
            'data' => $data,
            'groupID' => $groupID,
        ]);
    }
    
    /**
     * Route to create or update device properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return type
     */
    public function editShow(int $hubID, int $id) 
    {
        $item = Device::findOrCreate($id, $hubID);
        $groupPath = Room::getPath($item->room_id, ' / ');

        return view('admin.hubs.devices.device-edit', [
            'item' => $item,
            'groupPath' => $groupPath,
        ]);
    }
    
    /**
     * Route to create or update device properties.
     * 
     * @param DeviceRequest $request
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function editPost(DeviceRequest $request, int $hubID, int $id) 
    {
        Device::storeFromRequest($request, $hubID, $id);
        
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
        Device::deleteById($id);
        
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
        $hub = Hub::find($hubID);
        $data = [];
        switch ($hub->typ) {
            case 'software':
                foreach ($hub->softHosts as $host) {
                    $data[] = (object)[
                        'id' => $host->id,
                        'rom' => $host->type()->title,
                        'count' => $host->devices->count(),
                    ];
                }
                break;
            case 'orangepi':
                //
                break;
            case 'din':
                foreach ($hub->owHosts as $host) {
                    $data[] = (object)[
                        'id' => $host->id,
                        'rom' => $host->romAsString(),
                        'count' => $host->devices->count(),
                    ];
                }
                break;
        }
        
        return response()->json($data);
    }
    
    /**
     * Route for requesting a list of host channels by id.
     * 
     * @param string $typ [din, ow, software, variable]
     * @param int $hostID
     * @return type
     */
    public function hostChannelList(string $typ, int $hostID = null) 
    {
        $data = [];
        switch ($typ) {
            case 'din':
                $settings = Property::getDinSettings();
                $data = config('din.'.$settings->mmcu.'.channels');
                break;
            case 'ow':
                $host = OwHost::find($hostID);
                if ($host) {
                    $data = $host->channelsOfType();
                }
                break;
            case 'software':
                $host = SoftHost::find($hostID);
                if ($host) {
                    $data = $host->channelsOfType();
                }
                break;
            case 'orangepi':
                $data = array_keys(config('orangepi.channels'));
                break;
        }
        
        return response()->json($data);
    }
}
