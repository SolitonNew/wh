<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        $data = [
            17408,
            17408,
            16948,
        ];
        
        $w = $data[0];
        
        $b1 = $w & 0xff;
        $b2 = ($w & 0x00ff);
        
        return $b1.' '.$b2;
        
        
        return 'OK';
    }
}
