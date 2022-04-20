<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DB;

class TestController extends Controller
{
    public function test()
    {
        $res = DB::select('SELECT now() as t');
        
        $t = \Carbon\Carbon::parse($res[0]->t, 'UTC')->setTimezone(config('app.timezone'));
        
        dd($t->format('H:i:s'));
    }
}
