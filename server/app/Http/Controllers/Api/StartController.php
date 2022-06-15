<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Models\Property;

class StartController extends Controller
{
    public function getDataBeforeLogin()
    {
        return response()->json([
            'lang' => Lang::get('terminal'),
        ]);
    }
    
    public function getDataAfterLogin()
    {
        return response()->json([
            'app_controls' => config('devices.app_controls'),
            'columns' => Property::getWebColumns(),
        ]);
    }
}
