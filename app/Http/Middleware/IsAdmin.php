<?php

namespace aleafoodapi\Http\Middleware;

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
        if ($request->user()->is_admin) {
            return $next($request);
        }

        return response()->json(["message" => "User not allowed"])->setStatusCode(401);
    }
}
