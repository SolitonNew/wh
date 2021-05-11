<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PlanPartsModel;

class PlanIndexRequest extends FormRequest
{
    const LAST_VIEW_ID = 'PLAN_INDEX_ID';
    
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
        $data['id'] = $this->route('id');
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('id');
        
        if ($id) {
            $this->redirect = route('admin.plan');
            return [
                'id' => 'exists:plan_parts,id',
            ];
        } else {
            $newID = $this->getViewID();
            if ($newID) {
                $this->redirect = route('admin.plan', $newID);
                return [
                    'id' => 'required',
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
    public function getViewID()
    {
        $id = $this->session()->get(self::LAST_VIEW_ID);
        $this->storeLastViewID(null);
        
        if ($id) {
            return $id;
        } else {
            $item = PlanPartsModel::whereParentId(null)
                        ->orderBy('order_num', 'asc')
                        ->first();
            if ($item) {
                return $item->id;
            }
        }
        
        return null;
    }
}
