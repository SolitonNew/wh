<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PlanPartsModel;
use App\Models\VariablesModel;
use App\Http\Requests\Admin\PlanIndexRequest;
use App\Http\Requests\Admin\PlanRequest;
use App\Http\Requests\Admin\PlanMoveChildsRequest;
use App\Http\Requests\Admin\PlanImportRequest;
use App\Http\Requests\Admin\PlanLinkDeviceRequest;
use App\Http\Requests\Admin\PlanPortRequest;

class PlanController extends Controller
{
    /**
     * This is the index route for the system plan page to work.
     * 
     * @param int $id
     * @return type
     */
    public function index(PlanIndexRequest $request, int $id = null) 
    {
        // Load plan records with port records and with devices
        list($parts, $ports, $devices) = PlanPartsModel::listAllForIndex($id);
        
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
     * @return type
     */
    public function editShow(int $id, int $p_id = -1)
    {
        $item = PlanPartsModel::findOrCreate($id, $p_id);
        
        return view('admin.plan.plan-edit', [
            'item' => $item,
            'itemBounds' => $item->getBoundsRelativeParent(),
            'itemStyle' => $item->getStyle(),
        ]);
    }
    
    /**
     * Route to create or update plan entries.
     * 
     * @param PlanRequest $request
     * @param int $id
     * @param int $p_id
     * @return string
     */
    public function editPost(PlanRequest $request, int $id, int $p_id = -1)
    {
        PlanPartsModel::storeFromRequest($request, $id, $p_id);
        
        return 'OK';
    }
    
    /**
     * Route to delete plan entries.
     * 
     * @param type $id
     * @return string
     */
    public function delete(int $id) 
    {
        PlanPartsModel::deleteById($id);
        
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
    public function planClone(int $id, string $direction) 
    {
        PlanPartsModel::cloneNearby($id, $direction);
        
        return 'OK';
    }
    
    /**
     * Route to displaying the plan owner change window.
     * 
     * @param int $id
     * @return type
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
     * @return string
     */
    public function moveChildsPost(PlanMoveChildsRequest $request, int $id)
    {
        PlanPartsModel::moveChildsFromRequest($request, $id);
        
        return 'OK';
    }
    
    /**
     * Route to displaying the plan ordering window.
     * 
     * @param int $id
     * @return type
     */
    public function orderShow(int $id)
    {
        $data = PlanPartsModel::childList($id);
        
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
    public function orderPost(Request $request, int $id)
    {
        PlanPartsModel::setChildListOrdersFromRequest($request);
        
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
    public function move(int $id, float $newX, float $newY) 
    {
        PlanPartsModel::move($id, $newX, $newY);
        
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
    public function size(int $id, float $newW, float $newH)
    {
        PlanPartsModel::size($id, $newW, $newH);
        
        return 'OK';
    }
    
    /**
     * Route to import plan from file.
     * displaying window for choise file.
     * 
     * @return type
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
     * @return string
     */
    public function planImportPost(PlanImportRequest $request) 
    {
        PlanPartsModel::importFromRequest($request);
        
        return 'OK';
    }
    
    /**
     * Route to export plan entries to file.
     * Маршрут для экспорта плана системы.
     * The plan data is collected as nested (tree-like) objects and serialized 
     * as a json string.
     * 
     * @return type
     */
    public function planExport() 
    {
        $data = PlanPartsModel::exportToString();
        
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
     * @return type
     */
    public function linkDeviceShow(Request $request, int $planID, int $deviceID = -1) 
    {        
        $device = VariablesModel::findOrCreate($deviceID);
        
        if ($deviceID == -1) {
            $devices = PlanPartsModel::devicesForLink();
        } else {
            $devices = [];
            
            $device->label = $device->name.' '.($device->comm);
            $app_control = VariablesModel::decodeAppControl($device->app_control);
            $device->label .= ' '."'$app_control->label'";
        }
        
        $part = PlanPartsModel::find($planID);

        return view('admin.plan.plan-link-device', [
            'planID' => $planID,
            'deviceID' => $deviceID,
            'planPath' => PlanPartsModel::getPath($planID, ' / '),
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
     * @return string
     * @throws Exception
     */
    public function linkDevicePost(PlanLinkDeviceRequest $request, int $planID, int $deviceID = -1) 
    {
        PlanPartsModel::linkDeviceFromRequest($request, $planID, $deviceID);
        
        return 'OK';
    }
    
    /**
     * Route to remove the device from the plan etries.
     * 
     * @param int $deviceID
     * @return string
     */
    public function unlinkDevice(int $deviceID) 
    {
        PlanPartsModel::unlinkDevice($deviceID);
        
        return 'OK';
    }
    
    /**
     * This route is used to add or update the port item of the plan_part item.
     * 
     * @param Request $request
     * @param int $planID
     * @param int $portIndex
     * @return type
     */
    public function portEditShow(Request $request, int $planID, int $portIndex = -1)
    {
        $part = PlanPartsModel::find($planID);
        
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
     * @param int $portID
     * @return type
     */
    public function portEditPost(PlanPortRequest $request, int $planID, int $portIndex = -1) 
    {
        PlanPartsModel::storePortFromRequest($request, $planID, $portIndex);
        
        return 'OK';
    }
    
    /**
     * This route is used to delete the port element of the plan_parts item.
     * 
     * @param int $planID
     * @param int $portIndex
     * @return string
     */
    public function portDelete(int $planID, int $portIndex) 
    {
        PlanPartsModel::deletePortByIndex($planID, $portIndex);
        
        return 'OK';
    }
}
