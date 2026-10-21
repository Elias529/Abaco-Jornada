<?php

namespace App\Http\Controllers;

use App\Models\Ausencia;
use App\Models\FalloComun;
use App\Models\Jornada;
use App\Models\SolicitudVacaciones;
use App\Services\CupoVacaciones;
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
    public function __construct(
        private JornadaService $jornada,
        private NagerFestivos $festivos,
        private CupoVacaciones $cupo,
    ) {}

    public function index(Request $request): View
    {
        $anio = (int) $request->query('anio', now()->year);
        if ($anio < 2020 || $anio > now()->year + 1) {
            $anio = now()->year;
        }

        $mes = $request->query('mes') === null
            ? ($anio === now()->year ? now()->month : 1)
            : (int) $request->query('mes');
        if ($mes < 1 || $mes > 12) {
            $mes = $anio === now()->year ? now()->month : 1;
        }

        $cursor = Carbon::create($anio, $mes, 1)->startOfMonth();
        $anterior = $cursor->copy()->subMonth();
        $siguiente = $cursor->copy()->addMonth();
        $mesAnterior = $anterior->greaterThanOrEqualTo(Carbon::create(2020, 1, 1))
            ? ['anio' => $anterior->year, 'mes' => $anterior->month]
            : null;
        $mesSiguiente = $siguiente->lessThanOrEqualTo(Carbon::create(now()->year + 1, 12, 1))
            ? ['anio' => $siguiente->year, 'mes' => $siguiente->month]
            : null;

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
        $pendientes = $usuario->solicitudesVacaciones()
            ->where('estado', SolicitudVacaciones::PENDIENTE)
            ->get()
            ->flatMap(fn (SolicitudVacaciones $solicitud) => $this->cupo->queCuentan($usuario, $solicitud->starts_on, $solicitud->ends_on))
            ->flip();

        $datos = [
            'festivos' => $festivos,
            'mes' => $this->meses($anio, $festivos, $propias, $jornadas, $faltas->groupBy(fn (Ausencia $ausencia) => $ausencia->absence_date->toDateString()), $pendientes)[$mes - 1],
            'anio' => $anio,
            'mesAnterior' => $mesAnterior,
            'mesSiguiente' => $mesSiguiente,
            'sinCalendario' => $festivos->isEmpty(),
            'faltas' => $faltas,
            'fallos' => FalloComun::query()->orderByDesc('falla_date')->limit(12)->get(),
            'esJefa' => $request->user()->esJefa(),
            'diasVacaciones' => $this->cupo->restantes($usuario, now()->year),
        ];

        if ($request->query('fragmento') === '1') {
            return view('calendario.mes', $datos);
        }

        return view('calendario.index', $datos);
    }

    public function solicitar(Request $request): RedirectResponse
    {
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
        $persona = $request->user();

        if (! $this->cupo->dentroDelPlazo($hasta)) {
            return back()->with('aviso', 'Tienes dos meses para cogerlas. A partir de ahí desaparecen.');
        }

        $cuentan = $this->cupo->queCuentan($persona, $desde, $hasta);
        if ($cuentan->isEmpty()) {
            return back()->with('aviso', 'Esos días no cuentan: son fin de semana o festivo.');
        }

        if (! $this->cupo->cabe($persona, $desde, $hasta)) {
            return back()->with('aviso', 'No te quedan tantos días. El máximo es 22 al año.');
        }

        $solapa = $persona->solicitudesVacaciones()
            ->whereIn('estado', [SolicitudVacaciones::PENDIENTE, SolicitudVacaciones::APROBADA])
            ->whereDate('starts_on', '<=', $hasta->toDateString())
            ->whereDate('ends_on', '>=', $desde->toDateString())
            ->exists();

        if ($solapa) {
            return back()->with('aviso', 'Esas fechas ya están pedidas.')->withInput();
        }

        $nota = trim((string) ($datos['nota'] ?? ''));

        SolicitudVacaciones::create([
            'user_id' => $persona->id,
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
     * @param  Collection<string, mixed>  $pendientes
     * @return list<array{nombre: string, dias: list<array<string, mixed>|null>}>
     */
    private function meses(int $anio, Collection $festivos, Collection $propias, Collection $jornadas, Collection $faltas, Collection $pendientes): array
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
                $momento = Carbon::parse($fecha);
                $dias[] = [
                    'fecha' => $fecha,
                    'numero' => $dia,
                    'hoy' => $fecha === $hoy,
                    'finde' => $momento->isWeekend(),
                    'cuenta' => ! $momento->isWeekend() && ! $porDia->has($fecha),
                    'festivo' => $porDia->get($fecha),
                    'ausencia' => $propias->get($fecha),
                    'pendiente' => $pendientes->has($fecha),
                    'faltan' => $faltas->get($fecha, collect()),
                    'registro' => $jornada && $fecha < $hoy ? route('registro.show', $jornada) : null,
                ];
            }

            $meses[] = ['nombre' => $nombre, 'dias' => $dias];
        }

        return $meses;
    }
}
