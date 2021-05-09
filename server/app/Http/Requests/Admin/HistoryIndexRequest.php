<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HistoryIndexRequest extends FormRequest
{    
    const LAST_VIEW_DEVICE = 'STATISTICS-TABLE-ID';
    
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
     * 
     * @return type
     */
    public function rules()
    {        
        if ($this->session()->get(self::LAST_VIEW_DEVICE) > 0) {
            $this->redirect = route('admin.jurnal-history', $this->session()->get(self::LAST_VIEW_DEVICE));
            return [
                'id' => 'required',
            ];
        } else {
            return [];
        }
    }
    
    /**
     * 
     */
    protected function passedValidation()
    {
        $id = $this->route('id');
        
        $this->storeLastVisibleDeviceId($id);
    }
    
    /**
     * 
     * @param int $deviceId
     */
    public function storeLastVisibleDeviceId(int $deviceId = null) {
        $this->session()->put(self::LAST_VIEW_DEVICE, $deviceId);
    }
}
