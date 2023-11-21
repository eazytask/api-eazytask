<?php

namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user() && !auth()->user()->company_roles->first()->company->sub_domain) {
            if (auth()->user()->company_roles->contains('role',2) || auth()->user()->company_roles->contains('role',5) || auth()->user()->company_roles->contains('role',4)) {
                return $next($request);
            }
        }
        return send_response(false,"You don't have this access",[],401);
    }
}
