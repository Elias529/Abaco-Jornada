<?php

namespace App\Http\Controllers;

use App\Models\FalloComun;
use App\Models\Festivo;
use App\Services\JornadaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function __construct(private JornadaService $jornada) {}

    public function index(): View
    {
        $anio = now()->year;

        return view('calendario.index', [
            'festivos' => Festivo::query()->whereYear('holiday_date', $anio)->orderBy('holiday_date')->get(),
            'anio' => $anio,
            'sinCalendario' => $this->jornada->anioSinCalendario(now()),
            'fallos' => FalloComun::query()->orderByDesc('falla_date')->limit(12)->get(),
            'esResponsable' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'holiday_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:120'],
        ], [
            'holiday_date.required' => 'Elige el día.',
            'name.required' => 'Escribe el nombre del festivo.',
        ]);

        try {
            Festivo::create([
                'holiday_date' => $datos['holiday_date'],
                'name' => $datos['name'],
                'municipality' => JornadaService::CENTRO,
            ]);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                return back()->with('aviso', 'Ese festivo ya está cargado.')->withInput();
            }
            throw $e;
        }

        return back()->with('ok', 'Guardado. El festivo ya cuenta para toda la plantilla. Los días ya trabajados se quedan como se registraron.');
    }

    public function fallo(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'falla_date' => ['required', 'date'],
            'note' => ['required', 'string', 'max:200'],
        ], [
            'falla_date.required' => 'Elige el día.',
            'note.required' => 'Escribe qué falló.',
        ]);

        $this->jornada->falloComun($datos['falla_date'], trim($datos['note']));

        return back()->with('ok', 'Guardado. Ese aviso no entra en la cuenta de cada persona.');
    }
}
