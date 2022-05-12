<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test()
    {
        $data = [
            514,
            2,
            12,
        ];
        
        $w = $data[0];
        $cmd = $w & 0xff;
        $args = (($w & 0xff00) >> 8) - 1;
        $id = $data[1];
        $params = [];
        for ($i = 0; $i < $args; $i++) {
            $params[] = $data[$i + 2];
        }
        
        $res = [
            'cmd' => $cmd,
            'args' => $args,
            'id' => $id,
            'params' => $params,
        ];
        
        return print_r($res, true);
        
        return 'OK';
    }
}
