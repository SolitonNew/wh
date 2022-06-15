<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CamcorderHost;

class CamsController extends Controller
{
    /**
     * 
     * @return type
     */
    public function getData(Request $request)
    {
        $data = CamcorderHost::orderBy('name')->get();
        foreach ($data as $row) {
            $row->poster = $row->getThumbnailUrl($request->api_token);
            $row->video = $row->getVideoUrl($request->getHttpHost());
        }
        
        return response()->json($data);
    }
    
    /**
     * 
     * @param int $id
     * @return type
     */
    public function getThumbnail(int $id)
    {
        $cam = CamcorderHost::findOrFail($id);
        return response()->download($cam->getThumbnailFileName());
    }
}
