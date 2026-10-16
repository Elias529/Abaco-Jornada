<?php

namespace App\Http\Controllers;

use App\Models\Ausencia;
use App\Models\FalloComun;
use App\Models\Jornada;
use App\Models\SolicitudVacaciones;
use App\Services\JornadaService;
use App\Services\NagerFestivos;
use App\Support\DiaFestivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function __construct(private JornadaService $jornada, private NagerFestivos $festivos) {}

    public function index(Request $request): View
    {
        $anio = (int) $request->query('anio', now()->year);
        if ($anio < 2020 || $anio > now()->year + 1) {
            $anio = now()->year;
        }

        $usuario = $request->user();
        $festivos = $this->festivos->delAnio((string) $usuario->centro, $anio);
        $propias = $usuario->ausencias()->whereYear('absence_date', $anio)->get()
            ->keyBy(fn (Ausencia $ausencia) => $ausencia->absence_date->toDateString());
        $jornadas = Jornada::query()
            ->where('user_id', $usuario->id)
            ->whereYear('work_date', $anio)
            ->get()
            ->keyBy(fn (Jornada $jornada) => $jornada->work_date->toDateString());
        $faltas = $usuario->esJefa()
            ? Ausencia::query()->with('user')->whereYear('absence_date', $anio)->orderBy('absence_date')->get()
            : collect();

        return view('calendario.index', [
            'festivos' => $festivos,
            'centro' => $usuario->centro,
            'meses' => $this->meses($anio, $festivos, $propias, $jornadas, $faltas->groupBy(fn (Ausencia $ausencia) => $ausencia->absence_date->toDateString())),
            'anio' => $anio,
            'anioSiguiente' => $anio + 1,
            'puedeAvanzar' => $anio < now()->year + 1,
            'sinCalendario' => $festivos->isEmpty(),
            'faltas' => $faltas,
            'fallos' => FalloComun::query()->orderByDesc('falla_date')->limit(12)->get(),
            'esJefa' => $request->user()->esJefa(),
            'solicitudes' => $usuario->esJefa()
                ? collect()
                : $usuario->solicitudesVacaciones()->latest('id')->get(),
        ]);
    }

    public function solicitar(Request $request): RedirectResponse
    {
        abort_if($request->user()->esJefa(), 403);

        $datos = $request->validate([
            'desde' => ['required', 'date', 'after_or_equal:today'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
            'nota' => ['nullable', 'string', 'max:500'],
        ], [
            'desde.required' => 'Elige el primer día.',
            'desde.date' => 'Elige el primer día.',
            'desde.after_or_equal' => 'El primer día no puede ser anterior a hoy.',
            'hasta.required' => 'Elige el último día.',
            'hasta.date' => 'Elige el último día.',
            'hasta.after_or_equal' => 'El último día tiene que ser el primero o uno posterior.',
            'nota.max' => 'La nota puede tener hasta 500 caracteres.',
        ]);

        $desde = Carbon::parse($datos['desde'])->startOfDay();
        $hasta = Carbon::parse($datos['hasta'])->startOfDay();

        if ($desde->diffInDays($hasta) > 39) {
            return back()->with('aviso', 'Pide como máximo 40 días.')->withInput();
        }

        $solapa = $request->user()->solicitudesVacaciones()
            ->whereIn('estado', [SolicitudVacaciones::PENDIENTE, SolicitudVacaciones::APROBADA])
            ->whereDate('starts_on', '<=', $hasta->toDateString())
            ->whereDate('ends_on', '>=', $desde->toDateString())
            ->exists();

        if ($solapa) {
            return back()->with('aviso', 'Esas fechas ya están pedidas.')->withInput();
        }

        $nota = trim((string) ($datos['nota'] ?? ''));

        SolicitudVacaciones::create([
            'user_id' => $request->user()->id,
            'starts_on' => $desde->toDateString(),
            'ends_on' => $hasta->toDateString(),
            'nota' => $nota === '' ? null : $nota,
            'estado' => SolicitudVacaciones::PENDIENTE,
        ]);

        return back()->with('ok', 'Enviada. La jefa la verá en Consultas.');
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
        } catch (UniqueConstraintViolationException) {
            return back()->with('aviso', 'Ese festivo ya está cargado.')->withInput();
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

    /**
     * @param  Collection<int, DiaFestivo>  $festivos
     * @param  Collection<string, Ausencia>  $propias
     * @param  Collection<string, Jornada>  $jornadas
     * @param  Collection<string, Collection<int, Ausencia>>  $faltas
     * @return list<array{nombre: string, dias: list<array<string, mixed>|null>}>
     */
    private function meses(int $anio, Collection $festivos, Collection $propias, Collection $jornadas, Collection $faltas): array
    {
        $porDia = $festivos->keyBy(fn (DiaFestivo $festivo) => $festivo->holiday_date->toDateString());
        $hoy = now()->toDateString();
        $nombres = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
        ];
        $meses = [];

        foreach ($nombres as $indice => $nombre) {
            $inicio = Carbon::create($anio, $indice + 1, 1);
            $dias = array_fill(0, $inicio->dayOfWeekIso - 1, null);

            for ($dia = 1; $dia <= $inicio->daysInMonth; $dia++) {
                $fecha = Carbon::create($anio, $indice + 1, $dia)->toDateString();
                $jornada = $jornadas->get($fecha);
                $dias[] = [
                    'numero' => $dia,
                    'hoy' => $fecha === $hoy,
                    'finde' => Carbon::parse($fecha)->isWeekend(),
                    'festivo' => $porDia->get($fecha),
                    'ausencia' => $propias->get($fecha),
                    'faltan' => $faltas->get($fecha, collect()),
                    'registro' => $jornada && $fecha < $hoy ? route('registro.show', $jornada) : null,
                ];
            }

            $meses[] = ['nombre' => $nombre, 'dias' => $dias];
        }

        return $meses;
    }
}
