<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyStatus
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
        if (auth()->user() && !auth()->user()->company_roles->first()->company->sub_domain) {
            if ((auth()->user()->company_roles->first()->role != 1) && auth()->user()->company_roles->first()->company->status == 0) {
                
                $request->user()->token()->revoke();
                return send_response(false,"sorry! your company has temporarily blocked!",null,401);
            }else{
                return $next($request);
            }
        }
        return send_response(false,"You don't have this access",[],401);
    }
}
