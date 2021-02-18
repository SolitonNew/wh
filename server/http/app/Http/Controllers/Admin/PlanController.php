<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlanController extends Controller
{
    public function index(int $id = 1) {
        return view('admin.plan', [
            'partID' => $id,
        ]);
    }
    
    public function edit(Request $request, int $id) {
        
    }
    
    public function delete($id) {
        
    }
}
