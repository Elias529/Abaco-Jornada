<?php

namespace App\Http\Controllers;

use App\Models\AccionTecnica;
use App\Services\SistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class TecnicoController extends Controller
{
    public function __construct(private SistemaService $sistema) {}

    public function index(): View
    {
        return view('tecnico.index', [
            'base' => $this->sistema->baseDeDatos(),
            'entorno' => $this->sistema->entorno(),
            'copias' => $this->sistema->copias(),
            'acciones' => AccionTecnica::query()->with('autor')->latest('id')->limit(10)->get(),
        ]);
    }

    public function copia(Request $request): RedirectResponse
    {
        try {
            $nombre = $this->sistema->hacerCopia($request->user());
        } catch (RuntimeException $error) {
            return back()->with('aviso', $error->getMessage());
        }

        return back()->with('ok', 'Guardado. Copia '.$nombre.'.');
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmo' => ['accepted'],
        ], [
            'confirmo.accepted' => 'Marca la casilla para confirmar que quieres actualizar.',
        ]);

        try {
            $mensaje = $this->sistema->actualizar($request->user());
        } catch (RuntimeException $error) {
            return back()->with('aviso', $error->getMessage());
        }

        return back()->with('ok', $mensaje);
    }
}
