<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {       
        if (!auth()->check()) {
            return redirect(route('login'));
        }
        
        switch ($role) {
            case 'terminal':
                if (auth()->user()->ACCESS < 1) {
                    abort(404);
                }
                break;
            case 'admin':
                if (auth()->user()->ACCESS < 2) {
                    abort(404);
                }
                break;
        }
        
        return $next($request);
    }
}
