<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Requests\HistoryIndexRequest;
use App\Http\Controllers\Controller;
use App\Http\Services\HistoryService;
use App\Http\Models\VariablesModel;
use App\Http\Models\VariableChangesModel;

class HistoryController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_historyService;
    
    /**
     * 
     * @param HistoryService $historyService
     */
    public function __construct(HistoryService $historyService) 
    {
        $this->_historyService = $historyService;
    }
    
    /**
     * Index route to display device history data.
     * 
     * @param HistoryIndexRequest $request
     * @param int $id
     * @return type
     */
    public function index(HistoryIndexRequest $request, int $id = null) 
    {        
        $this->_historyService->storeFilterDataFromRequest($request);
        
        list($data, $errors) = $this->_historyService->getFilteringData($id);        
        
        $devices = VariablesModel::devicesListWithRoomName();
        
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
        $item = VariableChangesModel::find($id);
        
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
        VariableChangesModel::deleteById($id);
        
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
        $count = $this->_historyService->deleteAllVisibleValues($id);
        
        return 'OK: '.$count;
    }
}
