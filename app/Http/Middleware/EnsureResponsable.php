<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResponsable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->esResponsable()) {
            return redirect()->route('jornada')->with('aviso', 'Esta página es del responsable de equipo.');
        }

        return $next($request);
    }
}
