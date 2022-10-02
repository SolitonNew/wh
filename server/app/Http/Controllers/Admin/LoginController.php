<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     *
     * @return type
     */
    public function showLogin()
    {
        $user = Auth::user();

        if ($user && $user->access == 2) {
            return redirect('/');
        }

        return view('admin.auth.login');
    }

    /**
     *
     * @param Request $request
     * @return string
     */
    public function postLogin(Request $request) {
        $user = Auth::user();

        if ($user && $user->access == 2) {
            abort(401);
        }

        $user = User::whereLogin($request->login)
                    ->first();

        if ($user) {
            if (!app('hash')->check($request->password, $user->password)) {
                return redirect(route('login'));
            }
            // ----------------------------------------
            $user->api_token = Str::random(60);
            $user->save();

            return redirect(route('loginpage', ['api_token' => $user->api_token]));
        }

        return redirect(route('login'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function loginPage(Request $request)
    {
        return view('admin.auth.login-page');
    }

    /**
     *
     * @return type
     */
    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $user->api_token = null;
            $user->save();
        }
        return redirect('/');
    }
}
