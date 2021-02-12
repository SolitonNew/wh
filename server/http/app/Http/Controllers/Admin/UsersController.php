<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * 
     * @return type
     */
    public function index() {
        return view('admin.users');
    }
    
    /**
     * 
     * @param Request $request
     * @return type
     */
    public function append(Request $request) {
        $user = new \App\Http\Models\UsersModel();
        $user->login = $request->post('login');
        $user->password = bcrypt($request->post('password'));
        $user->save();
        
        return redirect(route('users'));
    }
}
