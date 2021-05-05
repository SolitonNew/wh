<?php

namespace App\Http\Requests;

use \App\Library\JsonFormRequest;

class UsersRequest extends JsonFormRequest
{
    public function rules()
    {
        $id = $this->route('id');
        return [
            'login' => 'required|string|unique:web_users,login,'.($id > 0 ? $id : ''),
            'password' => 'string|'.($id > 0 ? 'nullable' : 'required'),
            'email' => 'nullable|email|string',
        ];
    }
}
