<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;

class JwtMiddleware
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
        $jwt_token = $request->bearerToken();

        if ($jwt_token) {
            try {
                $credentials = JWT::decode($jwt_token, env('JWT_SECRET'), ['HS256']);

                return $next($request);
            } catch (\Exception $e) {
                return app('api.helper')->failed($e->getMessage(), []);
            }
        }

        return app('api.helper')->failed("Missing Token", [], 401);
    }
}
