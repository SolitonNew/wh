<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class PlanLinkDeviceRequest extends JsonFormRequest
{
    public function rules()
    {
        return [
            'offset' => 'numeric|required',
            'cross' => 'numeric|required',
        ];
    }
}
