<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebLogMem;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test()
    {
        DB::delete('delete from web_logs_mem');
        return 'OK';
    }
}
