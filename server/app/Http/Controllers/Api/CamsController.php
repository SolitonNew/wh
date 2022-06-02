<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Videcam;

class CamsController extends Controller
{
    /**
     * 
     * @return type
     */
    public function getData()
    {
        $data = Videcam::orderBy('name')->get();
        
        return response()->json($data);
    }
}
