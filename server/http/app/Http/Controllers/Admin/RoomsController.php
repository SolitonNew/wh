<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomsController extends Controller
{
    public function index(int $partID = 1) {
        return view('admin.rooms', [
            'partID' => $partID,
        ]);
    }
    
    public function edit(Request $request, int $id) {
        $item = \App\Http\Models\PlanPartsModel::find($id);
        
        if ($request->method() == 'POST') {
            
        } else {
            if (!$item) {
                $item = (object)[
                    'ID' => -1,
                    'NAME' => '',
                    'PARENT_ID' => -1,
                    'ORDER_NUM' => 0,
                ];
            }
            
            return view('admin.room-edit', [
                'item' => $item,
            ]);
        }
    }
    
    public function delete(int $id) {
        
    }
}
