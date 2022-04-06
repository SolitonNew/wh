<?php

namespace App\Http\Controllers;

class IndexController extends Controller 
{
    /**
     * 
     * @return type
     */
    public function index() 
    {
        return redirect(route('home'));
    }
}
