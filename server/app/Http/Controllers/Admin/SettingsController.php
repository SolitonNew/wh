<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\DaemonManager;
use App\Library\Daemons\DinDaemon;
use App\Library\Daemons\PyhomeDaemon;
use App\Library\Daemons\ZigbeeoneDaemon;
use App\Services\Admin\SettingsService;
use Illuminate\Http\Request;
use App\Models\Property;

class SettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    private SettingsService $service;

    /**
     * @param SettingsService $service
     */
    public function __construct(SettingsService $service)
    {
        $this->service = $service;
    }

    /**
     * Index route of the terminal settings module.
     *
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $levels = $this->service->levels();
        $currentLevel = $this->service->getCurrentLevel();

        return view('admin.settings.settings', [
            'levels' => $levels,
            'maxLevel' => $currentLevel,
        ]);
    }

    /**
     * This route is used to set the maximum value of the visible level of the
     * plan_rooms structure for the terminal module.
     *
     * @param type $value
     * @return string
     */
    public function setMaxLevel($value): string
    {
        $this->service->setCurrentLevel($value);
        return 'OK';
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setTimezone(Request $request): string
    {
        Property::setTimezone($request->timezone);
        return 'OK';
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setLocation(Request $request): string
    {
        Property::setLocation($request->latitude, $request->longitude);
        return 'OK';
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setDinSettings(Request $request, DaemonManager $daemonManager): string
    {
        DinDaemon::setSettings('PORT', $request->port);
        DinDaemon::setSettings('MMCU', $request->mmcu);
        // Reboot Daemon With New Settings
        $daemonManager->restart(DinDaemon::SIGNATURE);
        return 'OK';
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setPyhomeSettings(Request $request, DaemonManager $daemonManager): string
    {
        PyhomeDaemon::setSettings('PORT', $request->port);
        // Reboot Daemon With New Settings
        $daemonManager->restart(PyhomeDaemon::SIGNATURE);
        return 'OK';
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setZigbeeoneSettings(Request $request, DaemonManager $daemonManager): string
    {
        ZigbeeoneDaemon::setSettings('PORT', $request->port);
        // Reboot Daemon With New Settings
        $daemonManager->restart(ZigbeeoneDaemon::SIGNATURE);
        return 'OK';
    }

    /**
     * @param Request $request
     * @return string
     */
    public function setForecast(Request $request): string
    {
        Property::setForecastSettings(
            $request->TEMP,
            $request->P,
            $request->CC,
            $request->G,
            $request->H,
            $request->V,
            $request->WD,
            $request->WS,
            $request->MP
        );

        return 'OK';
    }

    /**
     * @return string
     */
    public function checkUpdates()
    {
        return view('admin.settings.check-update-dialog', [
            'response' => $this->service->checkUpdates(),
        ]);
    }
}
