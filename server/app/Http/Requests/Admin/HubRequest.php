<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class HubRequest extends JsonFormRequest
{
    public function rules() 
    {
        $id = $this->route('id');
        if ($this->typ == 'din') {
            return [
                'name' => 'string|required',
                'typ' => 'string|required',
                'rom' => 'numeric|required|min:1|max:15|unique:core_hubs,rom,'.($id > 0 ? $id : ''),
                'comm' => 'string|nullable',
            ];
        } else {
            return [
                'name' => 'string|required',
                'typ' => 'string|required',
                'comm' => 'string|nullable',
            ];
        }
    }
}
