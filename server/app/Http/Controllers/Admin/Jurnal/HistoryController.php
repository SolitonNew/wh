<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;
use App\Services\Admin\HistoryService;
use App\Models\Device;
use App\Models\DeviceChange;
use App\Models\Property;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_service;
    
    /**
     * 
     * @param HistoryService $service
     */
    public function __construct(HistoryService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * Index route to display device history data.
     * 
     * @param int $id
     * @return type
     */
    public function index(Request $request, int $id = null) 
    {        
        // Last view id  --------------------------
        if (!$id) {
            $id = Property::getLastViewID('HISTORY');
            if ($id && Device::find($id)) {
                return redirect(route('admin.jurnal-history', ['id' => $id]));
            }
            $id = null;
        }
        
        Property::setLastViewID('HISTORY', $id);
        Property::setLastViewID('JURNAL_PAGE', 'history');
        // ----------------------------------------
        
        $this->_service->storeFilterDataFromRequest($request);
        
        list($data, $errors) = $this->_service->getFilteringData($id);        
        
        $devices = Device::orderBy('name', 'asc')->get();
        
        return view('admin.jurnal.history.history', [
            'id' => $id,
            'devices' => $devices,
            'data' => $data,
        ])->withErrors($errors);
    }
    
    /**
     * This route to display device history by id.
     * 
     * @param int $id
     * @return type
     */
    public function valueView(int $id) 
    {
        $item = DeviceChange::find($id);
        
        return view('admin/jurnal/history/history-value', [
            'item' => $item,
        ]);
    }
    
    /**
     * This route to delete device history record by id.
     * 
     * @param int $id
     * @return string
     */
    public function valueDelete(int $id) 
    {
        DeviceChange::deleteById($id);
        
        return 'OK';
    }
    
    /**
     * This route to delete all visible device history reccords.
     * 
     * @param int $id
     * @return string
     */
    public function deleteAllVisibleValues(int $id) 
    {
        $count = $this->_service->deleteAllVisibleValues($id);
        
        return 'OK: '.$count;
    }
}
