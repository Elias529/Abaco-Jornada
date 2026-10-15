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
        $personaId = is_string($persona) && ctype_digit($persona) ? (int) $persona : null;
        $desde = $this->fecha($request->query('desde'));
        $hasta = $this->fecha($request->query('hasta'));

        if ($personaId !== null && ! User::query()->whereKey($personaId)->exists()) {
            $personaId = null;
        }

        $incidencias = Incidencia::query()
            ->with('autor')
            ->when($personaId !== null, fn ($q) => $q->where('user_id', $personaId))
            ->when($desde !== '', fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta !== '', fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->latest('id')
            ->get();

        return view('incidencias.index', [
            'incidencias' => $incidencias,
            'personas' => User::query()->orderBy('name')->get(),
            'personaId' => $personaId,
            'desde' => $desde,
            'hasta' => $hasta,
            'hoy' => now()->toDateString(),
        ]);
    }

    public function show(Incidencia $incidencia): View
    {
        return view('incidencias.show', [
            'incidencia' => $incidencia->load('autor'),
        ]);
    }

    private function fecha(mixed $valor): string
    {
        return is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) === 1 ? $valor : '';
    }
}
