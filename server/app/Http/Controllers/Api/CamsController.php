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
            $host = 'http://'.$request->getHttpHost();
            $poster = '';
            if (file_exists($row->getThumbnailFileName())) {
                $poster = route('cam-thumbnail', [
                    'id' => $row->id, 
                    'api_token' => $request->get('api_token')
                ]);
            }
            $row->poster = $poster;
            $row->video = $host.':'.(10000 + $row->id);
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
