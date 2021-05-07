<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Services\DemonsService;

class DemonsIndexRequest extends FormRequest
{
    /**
     * 
     * @return boolean
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
    public function rules(DemonsService $demonsService)
    {
        $this->redirect = route('admin.jurnal-demons', $demonsService->firstDemonId());
        
        return [
            'id' => 'required',
        ];
    }
}
