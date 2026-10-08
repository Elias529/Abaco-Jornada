<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJefe
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->esJefe()) {
            return redirect()->route('jornada')->with('aviso', 'Esta página es de la dirección.');
        }

        return $next($request);
    }
}
