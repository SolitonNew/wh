<?php

namespace App\Http\Requests\Admin;

use App\Library\JsonFormRequest;

class ScheduleRequest extends JsonFormRequest
{
    public function rules()
    {
        return [
            'comm' => 'required|string',
            'action' => 'required|string',
            'interval_time_of_day' => 'required|string',
            'interval_day_of_type' => 'string|'.(in_array($this->interval_type, [1, 2, 3]) ? 'required' : 'nullable'),
        ];
    }
}
