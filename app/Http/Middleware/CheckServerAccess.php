<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckServerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only admins can access server-level operations
        if (!$request->user() || $request->user()->role->name !== 'admin') {
            abort(403, 'Unauthorized server access');
        }

        return $next($request);
    }
}
