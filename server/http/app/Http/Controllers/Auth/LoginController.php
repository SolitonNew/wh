<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    
    /**
     * 
     * @return type
     */
    protected function redirectTo() {
        return route('home');
    }

    /**
     * 
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    /**
     * 
     * @return string
     */
    public function username()
    {
        return 'login';
    }
}
