<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Services\HubsService;

class HubsIndexRequest extends FormRequest
{
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
    public function rules(HubsService $hubsService)
    {
        $id = $this->route('id');
        if ($id) {
            return [
                'id' => 'exists:core_controllers,id',
            ];
        } else {
            $newId = $hubsService->getIdForView();
            if ($newId) {
                $this->redirect = route('admin.hub-devices', [$newId]);
                return [
                    'id' => 'required',
                ];
            } else {
                return [];
            }
        }
    }
}
