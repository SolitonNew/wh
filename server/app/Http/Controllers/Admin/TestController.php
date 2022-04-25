<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use DB;

class TestController extends Controller
{
    public function test()
    {        
        //\App\Models\SoftHostStorage::truncate();
        //dd('OK');
        
        $json = \App\Models\SoftHostStorage::where('soft_host_id', 2)
            ->orderBy('created_at', 'desc')
            ->first()
            ->data;
        
        $data = json_decode($json);
        
        dd($data);
        
        $time = parse_datetime(now())->startOfHour();
        $data = [];
        for ($i = 0; $i < 119; $i++) {
            $data[] = (object)[
                'time' => $time,
                
                'TEMP' => Device::getValue(141, $i),
                'P' => Device::getValue(142, $i),
                'CC' => Device::getValue(143, $i),
                'G' => Device::getValue(144, $i),
                'H' => Device::getValue(145, $i),
                'V' => Device::getValue(146, $i),
                'WD' => Device::getValue(147, $i),
                'WS' => Device::getValue(148, $i),
                'MP' => Device::getValue(149, $i),
                
                'TEMP_2' => Device::getValue(150, $i),
                'P_2' => Device::getValue(151, $i),
                'CC_2' => Device::getValue(152, $i),
                'G_2' => Device::getValue(153, $i),
                'H_2' => Device::getValue(154, $i),
                'V_2' => Device::getValue(155, $i),
                'WD_2' => Device::getValue(156, $i),
                'WS_2' => Device::getValue(157, $i),
                'MP_2' => Device::getValue(158, $i),
            ];
            $time = $time->copy()->addHours(1);
        }
        
        return view('admin/test.test', [
            'data' => $data,
        ]);
    }
}
