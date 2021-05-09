<?php

namespace App\Http\Controllers\Admin\Jurnal;

use App\Http\Controllers\Controller;

class PowerController extends Controller
{
    /**
     * This index route displays statistics on the system's capacity.
     * 
     * @return type
     */
    public function index() 
    {
        return view('admin.jurnal.power.power', [
            
        ]);
    }
}
