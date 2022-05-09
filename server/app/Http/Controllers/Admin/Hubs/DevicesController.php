<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Controllers\Controller;
use App\Services\Admin\DevicesService;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Room;
use App\Models\Hub;
use App\Models\OwHost;
use App\Models\I2cHost;
use App\Models\SoftHost;
use App\Models\Property;
use Illuminate\Support\Facades\Lang;

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
     * @param int $hubID
     * @param type $groupID
     * @return type
     */
    public function index(int $hubID = null, $groupID = null) 
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
        $appControls = Lang::get('admin/hubs.app_control');

        return view('admin.hubs.devices.device-edit', [
            'item' => $item,
            'groupPath' => $groupPath,
            'appControls' => $appControls,
        ]);
    }
    
    /**
     * Route to create or update device properties.
     * 
     * @param int $hubID
     * @param int $id
     * @return string
     */
    public function editPost(Request $request, int $hubID, int $id) 
    {
        return Device::storeFromRequest($request, $hubID, $id);
    }
    
    /**
     * Route to delete the device by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        return Device::deleteById($id);
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
                foreach ($hub->i2cHosts as $host) {
                    $data[] = (object)[
                        'id' => $host->id,
                        'rom' => $host->typ.' (0x'.dechex($host->address).')',
                        'count' => $host->devices->count(),
                    ];
                }
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
            case 'i2c':
                $host = I2cHost::find($hostID);
                if ($host) {
                    $data = $host->channelsOfType();
                }
                break;
        }
        
        return response()->json($data);
    }
}
