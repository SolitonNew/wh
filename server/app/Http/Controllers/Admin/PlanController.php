<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Device;
use App\Models\Property;
use Log;

class PlanController extends Controller
{
    /**
     * This is the index route for the system plan page to work.
     *
     * @param int $id
     * @return type
     */
    public function index(int $id = null)
    {
        // Last view id  --------------------------
        if (!$id) {
            $id = Property::getLastViewID('PLAN');
            if ($id && Room::find($id)) {
                return redirect(route('admin.plan', ['id' => $id]));
            }
            $id = null;
        }

        if (!$id) {
            $item = Room::whereParentId(null)
                ->orderBy('order_num', 'asc')
                ->first();
            if ($item) {
                return redirect(route('admin.plan', ['id' => $item->id]));
            }
        }

        Property::setLastViewID('PLAN', $id);
        // ----------------------------------------

        // Load plan records with port records and with devices
        list($parts, $ports, $devices) = Room::listAllForIndex($id);

        return view('admin.plan.plan', [
            'partID' => $id,
            'data' => $parts,
            'ports' => $ports,
            'devices' => $devices,
        ]);
    }

    /**
     * Route to create or update plan entries.
     *
     * @param int $id
     * @param int $p_id
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function editShow(int $id, int $p_id = -1)
    {
        $item = Room::findOrCreate($id, $p_id);

        return view('admin.plan.plan-edit', [
            'item' => $item,
            'itemBounds' => $item->getBoundsRelativeParent(),
            'itemStyle' => $item->getStyle(),
        ]);
    }

    /**
     * Route to create or update plan entries.
     *
     * @param Request $request
     * @param int $id
     * @param int $p_id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function editPost(Request $request, int $id, int $p_id = -1)
    {
        return Room::storeFromRequest($request, $id, $p_id);
    }

    /**
     * Route to delete plan entries.
     *
     * @param type $id
     * @return string
     */
    public function delete(int $id): string
    {
        Room::deleteById($id);
        return 'OK';
    }

    /**
     * Route to clone plan entries.
     * Makes a copy of the record but changes the coordinates of the new
     * record given the $ direction input parameter so that the new record
     * is adjacent to the original.
     *
     * @param int $id
     * @param string $direction
     * @return string
     */
    public function planClone(int $id, string $direction): string
    {
        Room::cloneNearby($id, $direction);
        return 'OK';
    }

    /**
     * Route to displaying the plan owner change window.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function moveChildsShow(int $id)
    {
        return view('admin.plan.plan-move-childs', [
            'partID' => $id,
        ]);
    }

    /**
     * Route to displaying the plan owner change window.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function moveChildsPost(Request $request, int $id)
    {
        return Room::moveChildsFromRequest($request, $id);
    }

    /**
     * Route to displaying the plan ordering window.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function orderShow(int $id)
    {
        $data = Room::childList($id);

        return view('admin.plan.plan-order', [
            'partID' => $id,
            'data' => $data,
        ]);
    }

    /**
     * Route to displaying the plan ordering window.
     *
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function orderPost(Request $request, int $id): string
    {
        Room::setChildListOrdersFromRequest($request);
        return 'OK';
    }

    /**
     * Route to move of the plan entries by id.
     *
     * @param int $id
     * @param float $newX
     * @param float $newY
     * @return string
     */
    public function move(int $id, float $newX, float $newY): string
    {
        Room::move($id, $newX, $newY);
        return 'OK';
    }

    /**
     * Route to resize of the plan entries.
     *
     * @param int $id
     * @param float $newW
     * @param float $newH
     * @return string
     */
    public function size(int $id, float $newW, float $newH): string
    {
        Room::size($id, $newW, $newH);
        return 'OK';
    }

    /**
     * Route to import plan from file.
     * displaying window for choise file.
     *
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function planImportShow()
    {
        return view('admin.plan.plan-import', []);
    }

    /**
     * Route to import plan from file.
     * run import.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function planImportPost(Request $request)
    {
        return Room::importFromRequest($request);
    }

    /**
     * Route to export plan entries to file.
     * Маршрут для экспорта плана системы.
     * The plan data is collected as nested (tree-like) objects and serialized
     * as a json string.
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function planExport()
    {
        $data = Room::exportToString();

        return response($data, 200, [
            'Content-Length' => strlen($data),
            'Content-Disposition' => 'attachment; filename="'.\Carbon\Carbon::now()->format('Ymd_His').'_plan.json"',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Route fro binding the device to plan entries and determines the
     * position of the device on the plan.
     *
     * @param Request $request
     * @param int $planID
     * @param int $deviceID
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function linkDeviceShow(Request $request, int $planID, int $deviceID = -1)
    {
        $device = Device::findOrCreate($deviceID);

        if ($deviceID == -1) {
            $devices = Room::devicesForLink();
        } else {
            $devices = [];

            $device->label = $device->name.' '.($device->comm);
            $app_control = Device::decodeAppControl($device->app_control);
            $device->label .= ' '."'$app_control->label'";
        }

        $part = Room::find($planID);

        return view('admin.plan.plan-link-device', [
            'planID' => $planID,
            'deviceID' => $deviceID,
            'planPath' => Room::getPath($planID, ' / '),
            'device' => $device,
            'devices' => $devices,
            'position' => $device->getPosition($request),
            'partBounds' => $part->getBounds(),
        ]);
    }

    /**
     * Route fro binding the device to plan entries and determines the
     * position of the device on the plan.
     *
     * @param Request $request
     * @param int $planID
     * @param int $deviceID
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function linkDevicePost(Request $request, int $planID, int $deviceID = -1)
    {
        return Room::linkDeviceFromRequest($request, $planID, $deviceID);
    }

    /**
     * Route to remove the device from the plan etries.
     *
     * @param int $deviceID
     * @return string
     */
    public function unlinkDevice(int $deviceID): string
    {
        Room::unlinkDevice($deviceID);
        return 'OK';
    }

    /**
     * This route is used to add or update the port item of the plan_part item.
     *
     * @param Request $request
     * @param int $planID
     * @param int $portIndex
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function portEditShow(Request $request, int $planID, int $portIndex = -1)
    {
        $part = Room::find($planID);

        return view('admin.plan.plan-port-edit', [
            'planID' => $planID,
            'portIndex' => $portIndex,
            'partBounds' => $part->getBounds(),
            'position' => $part->getPort($portIndex, $request),
        ]);
    }

    /**
     * This route is used to add or update the port item of the plan_part item.
     *
     * @param Request $request
     * @param int $planID
     * @param int $portIndex
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function portEditPost(Request $request, int $planID, int $portIndex = -1)
    {
        return Room::storePortFromRequest($request, $planID, $portIndex);
    }

    /**
     * This route is used to delete the port element of the plan_rooms item.
     *
     * @param int $planID
     * @param int $portIndex
     * @return string
     */
    public function portDelete(int $planID, int $portIndex): string
    {
        Room::deletePortByIndex($planID, $portIndex);
        return 'OK';
    }
}
