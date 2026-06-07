<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAkkezxla
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAkkezxla()) {
            abort(403);
        }

        return $next($request);
    }
}
