<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Services\ScriptsService;

class ScriptsIndexRequest extends FormRequest
{
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
    public function rules(ScriptsService $scriptsService)
    {
        $id = $this->route('id');
        
        if ($id) {
            return [
                'id' => 'exists:core_scripts,id',
            ];
        } else {
            $newId = $scriptsService->getIdForView();
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
}
