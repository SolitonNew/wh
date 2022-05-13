<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        /*\App\Models\ExtApiHost::truncate();
        \App\Models\OwHost::truncate();
        \App\Models\I2cHost::truncate(); */
        
        $a = [
            \App\Models\ExtApiHost::count(),
            \App\Models\OwHost::count(),
            \App\Models\I2cHost::count(),
        ];
        
        return print_r($a, true);
        
        return 'OK';
    }
}
