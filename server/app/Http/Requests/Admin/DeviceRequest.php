<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class DeviceRequest extends JsonFormRequest
{
    public function rules()
    {
        $id = $this->route('id');
        return [
            'hub_id' => 'required|numeric',
            'name' => 'required|string|unique:core_devices,name,'.($id > 0 ? $id : ''),
            'comm' => 'nullable|string',
            'ow_id' => ($this->typ === 'ow' ? 'required|numeric' : ''),
            'value' => 'nullable|numeric',
        ];
    }
}
