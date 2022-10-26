<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebLogMem;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test(\App\Services\Admin\Autotest $autotest)
    {
        //DB::delete('delete from web_logs_mem');
        
        $autotest->runForAllScripts();
        
        return 'OK';
    }
}
