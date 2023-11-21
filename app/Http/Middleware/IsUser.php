<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // dd(auth()->user());
        if(auth()->user()){
            if (auth()->user()->company_roles->contains('role',3) || auth()->user()->company_roles->contains('role',4)) {
                return $next($request);
            }else{
                return send_response(false,"You don't have this access",[],401);
            }
        }else{
            return send_response(false,"You don't have this access",[],401);
        }

        return $next($request);
    }
}
