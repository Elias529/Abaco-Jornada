<?php

namespace App\Http\Controllers;

use App\Models\AccionTecnica;
use App\Models\Ticket;
use App\Services\SistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TecnicoController extends Controller
{
    public function __construct(private SistemaService $sistema) {}

    public function index(): View
    {
        return view('tecnico.index', [
            'base' => $this->sistema->baseDeDatos(),
            'entorno' => $this->sistema->entorno(),
            'copias' => $this->sistema->copias(),
            'copiasRestauracion' => $this->sistema->copias(true),
            'acciones' => AccionTecnica::query()->with('autor')->latest('id')->limit(10)->get(),
            'ticketsPendientes' => Ticket::query()->where('estado', '!=', Ticket::RESUELTO)->count(),
        ]);
    }

    /**
     * Todos los tickets. Por defecto, los que siguen sin resolver.
     */
    public function tickets(Request $request): View
    {
        $filtro = $request->query('estado');
        $filtro = in_array($filtro, ['todos', Ticket::RESUELTO], true) ? $filtro : 'pendientes';

        $consulta = Ticket::query()->with('autor')->latest('id');

        if ($filtro === 'pendientes') {
            $consulta->where('estado', '!=', Ticket::RESUELTO);
        } elseif ($filtro === Ticket::RESUELTO) {
            $consulta->where('estado', Ticket::RESUELTO);
        }

        return view('tecnico.tickets', [
            'tickets' => $consulta->get(),
            'filtro' => $filtro,
            'pendientes' => Ticket::query()->where('estado', '!=', Ticket::RESUELTO)->count(),
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

    public function exportar(Request $request): BinaryFileResponse|RedirectResponse
    {
        try {
            $nombre = $this->sistema->hacerCopia($request->user());
            $ruta = $this->sistema->rutaDeCopia($nombre);
        } catch (RuntimeException $error) {
            return back()->with('aviso', $error->getMessage());
        }

        return response()->download($ruta, $nombre, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
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

    public function restaurar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'confirmo' => ['accepted'],
            'copia' => ['nullable', 'string', 'max:255'],
            'archivo' => ['nullable', 'file', 'max:102400'],
        ], [
            'confirmo.accepted' => 'Marca la casilla para confirmar que quieres sustituir la base.',
            'archivo.file' => 'Elige un archivo de base de datos.',
            'archivo.max' => 'Ese archivo es demasiado grande.',
        ]);

        $rutaSubida = $request->file('archivo')?->getRealPath() ?: null;

        try {
            $mensaje = $this->sistema->restaurar(
                $request->user(),
                $datos['copia'] ?? null,
                is_string($rutaSubida) ? $rutaSubida : null,
            );
        } catch (RuntimeException $error) {
            return back()->with('aviso', $error->getMessage())->with('restaurar', true);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('ok', $mensaje);

        return redirect()->route('login');
    }
}
