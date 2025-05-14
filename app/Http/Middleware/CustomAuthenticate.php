<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, ...$guards): Response
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            abort(401, 'Unauthenticated.');
        }
    }
}
