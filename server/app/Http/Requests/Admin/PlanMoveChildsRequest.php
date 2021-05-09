<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class PlanMoveChildsRequest extends JsonFormRequest
{
    public function rules()
    {
        return [
            'DX' => 'required|numeric',
            'DY' => 'required|numeric',
        ];
    }
}
