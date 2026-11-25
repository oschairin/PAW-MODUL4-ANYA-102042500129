<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, $role)
{
    if (!$request->user() || $request->user()->role !== $role) {
        return redirect('/')
            ->with('error', 'Akses Ditolak! Anda tidak memiliki hak akses.');
    }

    return $next($request);
}
}
