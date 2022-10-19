<?php

namespace App\Http\Controllers\Admin\Hubs;

use App\Http\Controllers\Controller;
use App\Services\Admin\HostsService;
use Illuminate\Http\Request;
use App\Models\ExtApiHost;
use App\Models\CamcorderHost;
use App\Models\OwHost;
use App\Models\I2cHost;
use App\Models\Property;
use App\Models\Hub;
use Illuminate\Support\Facades\Log;

class HostsController extends Controller
{
    /**
     * @var HostsService
     */
    private HostsService $service;

    /**
     * @param HostsService $service
     */
    public function __construct(HostsService $service)
    {
        $this->service = $service;
    }

    /**
     * This is an index route for displaying a list of host.
     * If the hub id does not exists, redirect to the owner route.
     *
     * @param int $hubID
     * @return type
     */
    public function index(int $hubID = null, string $group = '')
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

        if (!Hub::find($hubID)) {
            Property::setLastViewID('HUB', null);
            return redirect(route('admin.hubs'));
        }

        Property::setLastViewID('HUB', $hubID);
        Property::setLastViewID('HUB_PAGE', 'hosts');
        // ----------------------------------------

        $data = $this->service->getIndexList($hubID, $group);

        return view('admin.hubs.hosts.hosts', [
            'hubID' => $hubID,
            'page' => 'hosts',
            'data' => $data,
            'group' => $group,
        ]);
    }

    /**
     * @param int $hubID
     * @param string $group
     * @param int $id
     * @return mixed
     */
    public function editHostShow(int $hubID, string $group, int $id): mixed
    {
        switch ($group) {
            case 'ow':
                $item = OwHost::findOrCreate($hubID, $id);
                $hostTypID = $item->rom_1;
                break;
            case 'i2c':
                $item = I2cHost::findOrCreate($hubID, $id);
                $hostTypID = $item->typ;
                break;
            case 'extapi':
                $item = ExtApiHost::findOrCreate($hubID, $id);
                $hostTypID = $item->typ;
                break;
            case 'camcorder':
                $item = CamcorderHost::findOrCreate($hubID, $id);
                $hostTypID = $item->typ;
                break;
            default:
                $item = (object)[
                    'id' => -1,
                    'hub_id' => $hubID,
                    'comm' => '',
                ];
                $hostTypID = '';
        }

        return view('admin.hubs.hosts.host-edit', [
            'item' => $item,
            'group' => $group,
            'hostTypID' => $hostTypID,
            'hostTypList' => $this->service->getHostTypList($hubID),
            'hub' => Hub::find($hubID),
        ]);
    }

    /**
     * @param Request $request
     * @param int $hubID
     * @param string $group
     * @param int $id
     * @return mixed
     */
    public function editHostPost(Request $request, int $hubID, string $group, int $id): mixed
    {
        switch ($group) {
            case 'ow':
                return OwHost::storeFromRequest($request, $hubID, $id);
            case 'i2c':
                return I2cHost::storeFromRequest($request, $hubID, $id);
            case 'extapi':
                return ExtApiHost::storeFromRequest($request, $hubID, $id);
            case 'camcorder':
                return CamcorderHost::storeFromRequest($request, $hubID, $id);
        }
        return response()->json([
            'errors' => ['Host Group Not Found'],
        ]);
    }

    /**
     * @param int $hubID
     * @param string $group
     * @param int $id
     * @return mixed
     */
    public function deleteHost(int $hubID, string $group, int $id): mixed
    {
        switch ($group) {
            case 'ow':
                return OwHost::deleteById($id);
            case 'i2c':
                return I2cHost::deleteById($id);
            case 'extapi':
                return ExtApiHost::deleteById($id);
            case 'camcorder':
                return CamcorderHost::deleteById($id);
        }
        return response()->json([
            'errors' => ['Host Group Not Found'],
        ]);
    }
}
