<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\ScriptsRequest;
use App\Http\Controllers\Controller;
use App\Http\Models\ScriptsModel;
use Session;

class ScriptsController extends Controller
{
    /**
     * The index route to display a list of scripts.
     * 
     * @param int $scriptID
     * @return type
     */
    public function index(int $scriptID = null)
    {
        if (!$scriptID) {
            $scriptID = Session::get('SCRIPT_INDEX_ID');
            if (\App\Http\Models\ScriptsModel::find($scriptID)) {
                return redirect(route('admin.scripts', $scriptID));
            } else {
                $scriptID = null;
            }
        }
        
        if (!$scriptID) {
            $first = \App\Http\Models\ScriptsModel::orderBy('comm', 'asc')->first();
            if ($first) {
                return redirect(route('admin.scripts', $first->id));
            }
        }
        
        $item = \App\Http\Models\ScriptsModel::find($scriptID);
        if ($scriptID && !$item) {
            return redirect(route('admin.scripts', ''));
        }
        
        Session::put('SCRIPT_INDEX_ID', $scriptID);
        
        $list = ScriptsModel::listAll();
        
        return view('admin.scripts.scripts', [
            'scriptID' => $scriptID,
            'list' => $list,
            'data' => $item,
        ]);
    }
    
    /**
     * The route to create or update script record propertys.
     * 
     * @param int $id
     * @return view
     */
    public function editShow(int $id)
    {
        $item = ScriptsModel::findOrCreate($id);
        
        return view('admin.scripts.script-edit', [
            'item' => $item,
        ]);
    }
    
    /**
     * The route to create or update script record propertys.
     * 
     * @param ScriptsRequest $request
     * @param int $id
     * @return string
     */
    public function editPost(ScriptsRequest $request, int $id)
    {
        ScriptsModel::storeFromRequest($request, $id);
        
        return 'OK';
    }
    
    /**
     * The route to delete the script record by id.
     * 
     * @param int $id
     * @return string
     */
    public function delete(int $id) 
    {
        ScriptsModel::deleteById($id);
        
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
        ScriptsModel::storeDataFromRequest($request, $id);
        
        return 'OK';
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function attacheEventsShow(int $id)
    {
        $data = ScriptsModel::attachedDevicesIds($id);

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
        ScriptsModel::attachDevicesFromRequest($request, $id);
        
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
