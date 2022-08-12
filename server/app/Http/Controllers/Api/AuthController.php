<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Events\LogoutEvent;

class AuthController extends Controller
{
    /**
     * 
     * @param Request $request
     */
    public function login(Request $request)
    {
        $user = User::whereLogin($request->login)
                    ->first();
        
        if ($user) {
            if (!app('hash')->check($request->password, $user->password)) {
                return response()->json([
                    'errors' => ['ERROR'],
                ]);
            }
            // ----------------------------------------
            event(new LogoutEvent($user->api_token));
            // ----------------------------------------
            $user->api_token = Str::random(60);
            $user->save();
            
            return response()->json([
                'token' => $user->api_token,
            ]);
        }
        
        return response()->json([
            'errors' => ['ERROR'],
        ]);
    }
}
