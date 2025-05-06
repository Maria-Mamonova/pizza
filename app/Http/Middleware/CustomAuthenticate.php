<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomAuthenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->authenticate($request, $this->guards());
        return $next($request);
    }
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            abort(401, 'Unauthenticated.');
        }
    }
}
