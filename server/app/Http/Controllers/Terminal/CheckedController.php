<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Terminal\CheckedService;

class CheckedController extends Controller
{
    private $_checkedService;
    
    public function __construct(CheckedService $checkedService) 
    {
        $this->_checkedService = $checkedService;
    }
    
    /**
     * Route for favorites index page.
     * 
     * @return type
     */
    public function index() 
    {
        $rows = $this->_checkedService->webChecks();
        $charts = $this->_checkedService->getChartsFor($rows);
        $varSteps = $this->_checkedService->getVarStepsFor($rows);
        
        return view('terminal.checked', [
            'rows' => $rows,
            'charts' => $charts,
            'varSteps' => $varSteps,
        ]);
    }
    
    /**
     * Route to manage favorites page entries.
     * 
     * @param type $selKey
     * @return type
     */
    public function editAdd(int $selKey = 0) 
    {                
        $app_controls = $this->_checkedService->getAppControls();
        $checks = $this->_checkedService->getCheckedIDs();
        $data = $this->_checkedService->getCheckedForEdit($selKey);
        
        return view('terminal.checked-edit-add', [
            'page' => 'add',
            'selKey' => $selKey,
            'appControls' => $app_controls,
            'checks' => $checks,
            'data' => $data,
        ]);
    }
    
    /**
     * Route to add devices to favorites page.
     * 
     * @param int $id
     * @return string
     */
    public function editAdd_ADD(int $id) 
    {
        $this->_checkedService->addToChecked($id);
        
        return 'OK';
    }
    
    /**
     * Route to remove devices from favorites page.
     * 
     * @param int $id
     * @return string
     */
    public function editAdd_DEL(int $id) 
    {
        $this->_checkedService->delFromChecked($id);
        
        return 'OK';
    }
    
    /**
     * Route for ordering favorites page entries.
     * 
     * @return type
     */
    public function editOrder() 
    {
        $data = $this->_checkedService->getOrderList();
        
        return view('terminal.checked-edit-order', [
            'page' => 'order',
            'data' => $data,
        ]);
    }
    
    /**
     * Route to move up entries of favorites page.
     * 
     * @param int $id
     * @return string
     */
    public function editOrder_UP(int $id) 
    {
        $this->_checkedService->orderUp($id);
        
        return 'OK';
    }
    
    /**
     * Route to move down entries of favorites page
     * 
     * @param type $id
     * @return string
     */
    public function editOrder_DOWN($id) 
    {
        $this->_checkedService->orderDown($id);
        
        return 'OK';
    }

    /**
     * Route to change the displayed color of the device.
     * 
     * @return type
     */
    public function editColor() 
    {
        $data = $this->_checkedService->getWebColors();
        
        return view('terminal.checked-edit-color', [
            'page' => 'color',
            'data' => $data,
        ]);
    }
    
    /**
     * Route to action to change the displayed color of the device.
     * 
     * @param Request $request
     * @param type $action
     * @return string
     */
    public function editColor_ACTION(Request $request, $action) 
    {
        $this->_checkedService->setWebColorsFromRequest($request, $action);
        
        return 'OK';
    }
}
