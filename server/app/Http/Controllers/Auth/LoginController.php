<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    
    /**
     * Override method for correcting redirection
     * 
     * @return string
     */
    protected function redirectTo() 
    {
        switch (Auth::user()->access) {
            case 1:
                return route('home');
            case 2:
                return route('admin');
        }
        return '/';
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
