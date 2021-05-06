<?php

namespace App\Http\Requests;

use \App\Library\JsonFormRequest;

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
