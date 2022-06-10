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
            $poster = '';
            if (file_exists(base_path('storage/app/cam_posters/'.$row->id.'.jpg'))) {
                $poster = route('cam-posters', ['id' => $row->id, 'api_token' => $request->get('api_token')]);
            }
            $row->poster = $poster;
            $row->video = $host.':'.(10000 + $i);
            $i++;
        }
        
        return response()->json($data);
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function getPoster(int $id)
    {
        return response()->download(base_path('storage/app/cam_posters/'.$id.'.jpg'));
    }
}
