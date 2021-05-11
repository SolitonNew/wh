<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ControllersModel;

class HubsIndexRequest extends FormRequest
{
    const LAST_VIEW_ID = 'HUB_INDEX_ID';
    
    /**
     * Determine if the user is authorized to make this request.
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
        $data['hubID'] = $this->route('hubID');
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $hubID = $this->route('hubID');
        if ($hubID) {
            $this->redirect = route('admin.hubs');
            return [
                'hubID' => 'exists:core_controllers,id',
            ];
        } else {
            $newId = $this->getIdForView();
            if ($newId) {
                $this->redirect = route('admin.hub-devices', [$newId]);
                return [
                    'hubID' => 'required',
                ];
            } else {
                return [];
            }
        }
    }
    
    /**
     * 
     */
    protected function passedValidation()
    {
        $hubID = $this->route('hubID');
        $this->storeLastVisibleId($hubID);
    }
    
    /**
     * 
     * @param int $id
     */
    public function storeLastVisibleId(int $id = null)
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
        $this->session()->put(self::LAST_VIEW_ID, null);
        if (!$id) {
            $item = ControllersModel::orderBy('name', 'asc')->first();
            if ($item) {
                $id = $item->id;
            }
        }
        
        return $id;
    }
    
}
