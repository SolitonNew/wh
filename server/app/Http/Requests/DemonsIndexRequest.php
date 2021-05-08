<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Library\DemonManager;

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
    public function rules(DemonManager $demonManager)
    {
        $this->redirect = route('admin.jurnal-demons', $demonManager->demons()[0]);
        
        return [
            'id' => 'required',
        ];
    }
}
