<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class PlanImportRequest extends JsonFormRequest
{
    public function rules()
    {
        return [
            'file' => 'file|required',
        ];
    }    
}
