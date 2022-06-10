<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Videcam;

class CamsController extends Controller
{
    /**
     * 
     * @return type
     */
    public function getData(Request $request)
    {
        $data = Videcam::orderBy('name')->get();
        
        $i = 0;
        foreach ($data as $row) {
            $host = 'http://'.$request->getHttpHost();
            $posterName = '/img/cams/'.$row->id.'.jpg';
            $row->poster = file_exists(base_path('public'.$posterName)) ? $host.$posterName : '';
            $row->video = $host.':'.(10000 + $i);
            $i++;
        }
        
        return response()->json($data);
    }
}
