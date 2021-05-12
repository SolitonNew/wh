<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class CamsRequest extends JsonFormRequest
{
    public function rules()
    {
        $id = $this->route('id');
        
        return [
            'name' => 'required|string|unique:plan_videcams,name,'.($id > 0 ? $id : ''),
            'url' => 'required|string',
            'url_low' => 'required|string',
            'url_high' => 'required|string',
        ];
    }
}
