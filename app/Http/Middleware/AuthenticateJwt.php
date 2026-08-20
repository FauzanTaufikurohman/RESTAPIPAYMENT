<?php

namespace App\Http\Middleware;

use App\Support\JwtToken;
use Closure;
use Illuminate\Http\Request;

class AuthenticateJwt
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization', '');
        $token = null;

        if (is_string($authorization) && $authorization !== '') {
            $authorization = trim($authorization);

            if (str_contains(strtolower($authorization), 'bearer ')) {
                $token = trim(substr($authorization, 7));
            } elseif (str_contains(strtolower($authorization), 'jwt ')) {
                $token = trim(substr($authorization, 4));
            } else {
                $token = $authorization;
            }
        }

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = JwtToken::userFromToken($token);

        if (! $user) {
            return response()->json(['message' => 'Invalid or expired token.'], 401);
        }

        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
