<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class PlanPortRequest extends JsonFormRequest
{
    public function rules()
    {
        return [
            'offset' => 'numeric|required',
            'width' => 'numeric|required',
            'depth' => 'numeric|required',
        ];
    }
}
