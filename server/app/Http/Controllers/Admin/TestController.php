<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use DB;

class TestController extends Controller
{
    public function test()
    {
        $values = [];
        for ($i = 0; $i < 100; $i++) {
            $values[] = Device::getValue(37, $i);
        }
        dd($values);
    }
}
