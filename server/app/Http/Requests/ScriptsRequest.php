<?php

namespace App\Http\Requests;

use \App\Library\JsonFormRequest;

class ScriptsRequest extends JsonFormRequest
{
    public function rules() {
        $id = $this->route('id');
        
        return [
            'comm' => 'required|string|unique:core_scripts,comm,'.($id > 0 ? $id : ''),
        ];
    }
}
