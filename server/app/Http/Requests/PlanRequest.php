<?php

namespace App\Http\Requests;

use \App\Library\JsonFormRequest;

class PlanRequest extends JsonFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'X' => 'required|numeric',
            'Y' => 'required|numeric',
            'W' => 'required|numeric',
            'H' => 'required|numeric',
            'pen_width' => 'nullable|numeric',
            'name_dx' => 'nullable|numeric',
            'name_dy' => 'nullable|numeric',
        ];
    }
}
