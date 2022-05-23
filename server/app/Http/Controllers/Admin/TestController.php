<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        $data = \App\Models\ExtApiHostStorage::orderBy('id')->get();
        dd($data);
    }
}
