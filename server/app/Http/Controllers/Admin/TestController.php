<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        $data = [];
        foreach (\App\Models\ExtApiHostStorage::orderBy('id')->get() as $item) {
            $data[] = json_decode($item->data);
        }
        dd($data);
    }
}
