<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidenciaController extends Controller
{
    public function index(Request $request): View
    {
        $persona = $request->query('persona');
        $fecha = $request->query('fecha');
        $personaId = is_string($persona) && ctype_digit($persona) ? (int) $persona : null;
        $fecha = is_string($fecha) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1 ? $fecha : '';

        if ($personaId !== null && ! User::query()->whereKey($personaId)->exists()) {
            $personaId = null;
        }

        $incidencias = Incidencia::query()
            ->with('autor')
            ->when($personaId !== null, fn ($q) => $q->where('user_id', $personaId))
            ->when($fecha !== '', fn ($q) => $q->whereDate('created_at', $fecha))
            ->latest('id')
            ->get();

        return view('incidencias.index', [
            'incidencias' => $incidencias,
            'personas' => User::query()->orderBy('name')->get(),
            'personaId' => $personaId,
            'fecha' => $fecha,
            'hoy' => now()->toDateString(),
        ]);
    }

    public function show(Incidencia $incidencia): View
    {
        return view('incidencias.show', [
            'incidencia' => $incidencia->load('autor'),
        ]);
    }
}
