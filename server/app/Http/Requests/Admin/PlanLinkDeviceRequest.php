<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class PlanLinkDeviceRequest extends JsonFormRequest
{
    public function rules()
    {
        $deviceID = $this->route('deviceID');
        
        return [
            'device' => ($deviceID > 0) ? '' : 'required',
            'offset' => 'numeric|required',
            'cross' => 'numeric|required',
        ];
    }
}
