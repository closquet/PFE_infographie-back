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
        $user = $request->user();

        if (!$user) {
            return response()->json(["message" => "Unauthenticated"])->setStatusCode(401);
        }

        if ($user && $user->is_admin) {
            return $next($request);
        }

        return response()->json(["message" => "User not allowed"])->setStatusCode(401);
    }
}
