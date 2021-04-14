<?php

namespace App\Http\Controllers\Admin\Jurnal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PowerController extends Controller
{
    public function index() 
    {
        return view('admin.jurnal.power.power', [
            
        ]);
    }
}
