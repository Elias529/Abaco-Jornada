<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las pantallas de fichaje, de registro propio y de gestión del equipo no son
 * para la dirección: ahí se escribe, y la dirección solo consulta.
 */
class EnsureFichaje
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->esJefe()) {
            return $next($request);
        }

        $respuesta = redirect()->route('jefe.index');

        if ($request->isMethodSafe()) {
            return $respuesta;
        }

        return $respuesta->with('aviso', 'La dirección consulta el registro. No ficha ni cambia horas ni horarios.');
    }
}
