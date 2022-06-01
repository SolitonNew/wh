<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller 
{
    /**
     * 
     * @return type
     */
    public function index(Request $request) 
    {
        return file_get_contents(base_path().'/public/index.html');
        
        /*if ($request->api_token) {
            return redirect(route('home', ['api_token' => $request->api_token]));
        } else {
            return redirect(route('home'));
        }*/
    }
}
