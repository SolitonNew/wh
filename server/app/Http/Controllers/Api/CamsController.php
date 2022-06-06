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
        
        $i = 0;
        foreach ($data as &$row) {
            $row->stream_port = (10000 + $i);
            $i++;
        }
        
        return response()->json($data);
    }
}
