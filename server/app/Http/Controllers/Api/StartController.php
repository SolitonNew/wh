<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class StartController extends Controller
{
    public function getData()
    {
        return response()->json([
            'lang' => Lang::get('terminal'),
            'app_controls' => config('devices.app_controls'),
        ]);
    }
}
