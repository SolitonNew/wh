<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\ScriptsService;
use App\Models\Script;
use App\Models\Property;

class ScriptsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $_service;
    
    /**
     * 
     * @param ScriptsService $service
     */
    public function __construct(ScriptsService $service) 
    {
        $this->_service = $service;
    }
    
    /**
     * The index route to display a list of scripts.
     * 
     * @param int $scriptID
     * @return type
     */
    public function index(int $id = null)
    {        
        // Last view id  --------------------------
        if (!$id) {
            $id = Property::getLastViewID('SCRIPT');
            if ($id && Script::find($id)) {
                return redirect(route('admin.scripts', ['id' => $id]));
            }
            $id = null;
        }
        
        if (!$id) {
            $item = Script::orderBy('comm', 'asc')->first();
            if ($item) {
                return redirect(route('admin.scripts', ['id' => $item->id]));
            }
        }
        
        Property::setLastViewID('SCRIPT', $id);
        // ----------------------------------------
        
        
        $list = Script::listAll();
        $item = Script::find($id);
        
        return view('admin.scripts.scripts', [
            'scriptID' => $id,
            'list' => $list,
            'data' => $item,
        ]);
    }
    
    /**
     * The route to create or update script record properties.
     * 
     * @param int $id
     * @return view
     */
    public function editShow(int $id)
    {
        $item = Script::findOrCreate($id);
        
        return view('admin.scripts.script-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * The route to create or update script record properties.
     * 
     * @param int $id
     * @return string
     */
    public function editPost(Request $request, int $id)
    {
        return Script::storeFromRequest($request, $id);
    }
    
    /**
     * The route to delete the script record by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        Script::deleteById($id);
        
        return 'OK';
    }
    
    /**
     * The route to save the source code of the script.
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function saveScript(Request $request, int $id) 
    {
        Script::storeDataFromRequest($request, $id);
        
        return 'OK';
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function attacheEventsShow(int $id)
    {
        $data = Script::attachedDevicesIds($id);

        return view('admin.scripts.script-events', [
            'id' => $id,
            'data' => $data,
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return string
     */
    public function attacheEventsPost(Request $request, int $id)
    {
        Script::attachDevicesFromRequest($request, $id);
        
        return 'OK';
    }
    
    /**
     * The route performs a script test.
     * 
     * @param Request $request
     */
    public function scriptTest(Request $request) 
    {
        return \App\Library\Script\ScriptEditor::scriptTest($request->command);
    }
}
