<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Library\DaemonManager;

class DaemonsIndexRequest extends FormRequest
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
    public function rules(DaemonManager $daemonManager)
    {
        $this->redirect = route('admin.jurnal-daemons', $daemonManager->daemons()[0]);
        
        return [
            'id' => 'required',
        ];
    }
}
