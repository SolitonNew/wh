<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Services\HistoryService;

class HistoryIndexRequest extends FormRequest
{    
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }

    public function rules()
    {        
        if ($this->session()->get(HistoryService::LAST_VIEW_DEVICE) > 0) {
            $this->redirect = route('admin.jurnal-history', $this->session()->get(HistoryService::LAST_VIEW_DEVICE));
            return [
                'id' => 'required',
            ];
        } else {
            return [];
        }
    }
}
