<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Models\ScriptsModel;

class ScriptsIndexRequest extends FormRequest
{
    const LAST_VIEW_ID = 'SCRIPT_INDEX_ID';
    
    /**
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * 
     * @param type $keys
     * @return type
     */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
    
    /**
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('id');
        
        if ($id) {
            $this->redirect = route('admin.scripts');
            return [
                'id' => 'exists:core_scripts,id',
            ];
        } else {
            $newId = $this->getIdForView();
            if ($newId) {
                $this->redirect = route('admin.scripts', $newId);
                return [
                    'id' => 'required',
                ];
            } else {
                return [];
            }
        }
    }
    
    protected function passedValidation()
    {
        $id = $this->route('id');
        $this->storeLastViewID($id);
    }
    
    /**
     * 
     * @param int $id
     */
    public function storeLastViewID(int $id = null)
    {
        $this->session()->put(self::LAST_VIEW_ID, $id);
    }
    
    /**
     * 
     * @return type
     */
    public function getIdForView()
    {
        $id = $this->session()->get(self::LAST_VIEW_ID);
        $this->storeLastViewID(null);
        if (!$id) {
            $item = ScriptsModel::orderBy('comm', 'asc')->first();
            if ($item) {
                $id = $item->id;
            }
        }
        
        return $id;
    }
}
