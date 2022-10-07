<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Firmware\Din;
use App\Library\Firmware\Pyhome;
use App\Library\Firmware\ZigbeeOne;
use Illuminate\Http\Request;
use App\Services\Admin\HubsService;
use App\Models\Hub;
use App\Models\Property;

class HubsController extends Controller
{
    /**
     * @var HubsService
     */
    private HubsService $service;

    /**
     * @param HubsService $service
     */
    public function __construct(HubsService $service)
    {
        $this->service = $service;
    }

    /**
     * This is index route.
     * If the hub exists, to redirect to the device page.
     *
     * @param int|null $hubID
     * @return type
     */
    public function index(int $hubID = null)
    {
        // Last view id  --------------------------
        $page = Property::getLastViewID('HUB_PAGE') ?: 'hosts';
        if (!$hubID) {
            $hubID = Property::getLastViewID('HUB');
            if ($hubID && Hub::find($hubID)) {
                return redirect(route('admin.hub-'.$page, ['hubID' => $hubID]));
            }
            $hubID = null;
        }

        if (!$hubID) {
            $item = Hub::orderBy('name', 'asc')->first();
            if ($item) {
                return redirect(route('admin.hub-'.$page, ['hubID' => $item->id]));
            }
        }

        Property::setLastViewID('HUB', $hubID);
        Property::setLastViewID('HUB_PAGE', 'hosts');
        // ----------------------------------------

        return view('admin/hubs/hubs', [
            'hubID' => $hubID,
        ]);
    }

    /**
     *  Route to create or update a hub property.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
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
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function editPost(Request $request, int $id)
    {
        return Hub::storeFromRequest($request, $id);
    }

    /**
     * Route to delete the hub by id.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function delete(int $id)
    {
        return Hub::deleteById($id);
    }

    /**
     * This route scans child hosts.
     * Returns a view with scan dialog report.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function hubNetworkScan(int $id)
    {
        $hub = Hub::find($id);

        switch ($hub->typ) {
            case 'din':
                $text = $this->service->dinHubsScan();
                break;
            case 'orangepi':
                $text = $this->service->orangepiHubScan();
                break;
            default:
                $text = 'It is impossible';
        }

        return view('admin.hubs.hub-network-scan', [
            'data' => $text,
        ]);
    }

    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function configWizardShow()
    {
        $hubs = Hub::getSortListFirmwareHubs();
        return view('admin.hubs.config-wizard', [
            'hubs' => $hubs,
            'hubIds' => array_values($hubs->pluck('id')->toArray()),
        ]);
    }

    /**
     * @param string $typ
     * @return string
     */
    public function configWizardMake(string $typ)
    {
        return $this->service->firmwareMake($typ);
    }

    /**
     * @return string
     */
    public function configWizardTransmit()
    {
        $this->service->configWizardTransmit();
        return 'OK';
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function configWizardStatus(Request $request)
    {
        return $this->service->configWizardStatus($request->ids);
    }

    /**
     * This route sends the din-daemon command to start uploading firmware
     * to the controllers.
     *
     * @return string
     */
    public function firmwareStart(): string
    {
        $this->service->firmwareStart();
        return 'OK';
    }

    /**
     * This route to query the firmware status now.
     *
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function firmwareStatus()
    {
        return $this->service->firmwareStatus();
    }

    /**
     * This route sends the din-daemon command to reboot all hubs.
     *
     * @return string
     */
    public function hubsReset(): string
    {
        $this->service->hubsReset();
        return 'OK';
    }

    /**
     * This route creates devices for all hosts, if that devices are not exists.
     *
     * @param int $hubID
     * @return string
     */
    public function addDevicesForAllHosts(int $hubID): string
    {
        $this->service->generateDevsByHub($hubID);
        return 'OK';
    }
}
