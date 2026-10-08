<?php

namespace App\Http\Controllers;

use App\Exceptions\ReglaJornada;
use App\Models\Consulta;
use App\Models\Correccion;
use App\Models\Explicacion;
use App\Models\Jornada;
use App\Models\Tramo;
use App\Models\User;
use App\Services\JornadaService;
use App\Support\Tiempo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistroController extends Controller
{
    public function __construct(private JornadaService $jornada) {}

    public function index(Request $request): View
    {
        return $this->mostrar($request->user(), $request->user(), false);
    }

    public function de(Request $request, User $user): View
    {
        if ($motivo = $this->pedirMotivo($request, $user)) {
            return $motivo;
        }

        return $this->mostrar($request->user(), $user, true);
    }

    public function motivo(Request $request, User $user): RedirectResponse
    {
        $this->autorizar($request->user(), $user);

        $datos = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Escribe el motivo de la consulta.',
            'reason.max' => 'El motivo puede tener hasta 500 caracteres.',
        ]);

        Consulta::create([
            'viewer_id' => $request->user()->id,
            'subject_id' => $user->id,
            'reason' => trim($datos['reason']),
        ]);

        $request->session()->put('consulta.'.$user->id, true);

        return redirect()->intended(route('equipo.registro', $user))
            ->with('ok', 'Guardado. Esta consulta queda escrita.');
    }

    public function show(Request $request, Jornada $jornada): View
    {
        $jornada->load('user', 'tramos.correcciones.autor');
        $this->autorizar($request->user(), $jornada->user);

        if ($motivo = $this->pedirMotivo($request, $jornada->user)) {
            return $motivo;
        }

        return view('registro.show', [
            'jornada' => $jornada,
            'persona' => $jornada->user,
            'total' => Tiempo::texto($this->jornada->minutosDeJornada($jornada)),
            'porEncima' => $this->jornada->porEncima($jornada->user, $jornada),
            'puedeCorregir' => $request->user()->esResponsable()
                || ($request->user()->id === $jornada->user_id && $jornada->work_date->gte(now()->subDays(7)->startOfDay())),
            'motivos' => Correccion::MOTIVOS,
            'esResponsable' => $request->user()->esResponsable(),
        ]);
    }

    public function corregir(Request $request, Tramo $tramo): RedirectResponse
    {
        $hora = $request->input('hora');
        if (is_string($hora) && strlen($hora) >= 5) {
            $request->merge(['hora' => substr($hora, 0, 5)]);
        }

        $datos = $request->validate([
            'campo' => ['required', 'in:started_at,ended_at'],
            'hora' => ['required', 'date_format:H:i'],
            'motivo' => ['required', 'in:'.implode(',', array_keys(Correccion::MOTIVOS))],
            'nota' => ['nullable', 'string', 'max:500'],
        ], [
            'hora.required' => 'Elige una hora.',
            'hora.date_format' => 'Elige una hora.',
            'motivo.required' => 'Elige un motivo.',
            'nota.max' => 'La nota puede tener hasta 500 caracteres.',
        ]);

        try {
            $mensaje = $this->jornada->corregir(
                $request->user(),
                $tramo,
                $datos['campo'],
                $datos['hora'],
                $datos['motivo'],
                $datos['nota'] ?? null,
            );
        } catch (ReglaJornada $e) {
            return back()->with('aviso', $e->getMessage());
        }

        return back()->with('ok', $mensaje);
    }

    public function explicar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'body' => ['required', 'string', 'max:500'],
            'work_date' => ['required', 'date'],
        ], [
            'body.required' => 'Escribe la explicación.',
            'body.max' => 'La nota puede tener hasta 500 caracteres.',
            'work_date.required' => 'Elige el día.',
        ]);

        Explicacion::create([
            'user_id' => $request->user()->id,
            'body' => trim($datos['body']),
            'work_date' => $datos['work_date'],
        ]);

        $this->jornada->marcarExplicados($request->user(), $datos['work_date']);

        return back()->with('ok', 'Guardado. Tu explicación queda en el registro.');
    }

    public function csv(Request $request): StreamedResponse
    {
        return $this->descargar($request->user(), $request->user());
    }

    public function csvDe(Request $request, User $user): StreamedResponse|View
    {
        if ($motivo = $this->pedirMotivo($request, $user)) {
            return $motivo;
        }

        return $this->descargar($request->user(), $user);
    }

    private function mostrar(User $visor, User $persona, bool $consulta): View
    {
        $this->autorizar($visor, $persona);

        $jornadas = Jornada::query()
            ->with('tramos')
            ->where('user_id', $persona->id)
            ->orderByDesc('work_date')
            ->limit(60)
            ->get();

        return view('registro.index', [
            'persona' => $persona,
            'consulta' => $consulta,
            'jornadas' => $jornadas,
            'servicio' => $this->jornada,
            'explicaciones' => $persona->explicaciones()->latest()->limit(10)->get(),
            'consultas' => $consulta
                ? Consulta::query()->with('viewer')->where('subject_id', $persona->id)->latest()->limit(10)->get()
                : collect(),
            'esResponsable' => $visor->esResponsable(),
            'propio' => $visor->id === $persona->id,
        ]);
    }

    private function descargar(User $visor, User $persona): StreamedResponse
    {
        $this->autorizar($visor, $persona);

        $jornadas = Jornada::query()
            ->with('tramos.correcciones.autor')
            ->where('user_id', $persona->id)
            ->orderBy('work_date')
            ->get();

        $nombre = 'registro-'.$persona->id.'.csv';

        return response()->streamDownload(function () use ($jornadas, $persona) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'Fecha',
                'Inicio',
                'Fin',
                'Inicio anterior',
                'Fin anterior',
                'Cambiado por',
                'Cambiado el',
                'Anotado a las',
                'Fuera del equipo',
                'Pausas',
                'Total',
                'Por encima del horario',
            ], ';');

            foreach ($jornadas as $jornada) {
                $trabajo = $jornada->tramos->where('tipo', Tramo::TRABAJO);
                $pausas = $jornada->tramos->where('tipo', Tramo::PAUSA);
                $primero = $trabajo->first();
                $ultimo = $jornada->tramos->filter(fn (Tramo $tramo) => $tramo->ended_at)->sortBy('ended_at')->last();
                $porEncima = $this->jornada->porEncima($persona, $jornada);
                $correcciones = $jornada->tramos->flatMap->correcciones->sortBy('created_at');
                $inicioAnterior = $correcciones->first(fn ($correccion) => $correccion->field === 'started_at');
                $finAnterior = $correcciones->first(fn ($correccion) => $correccion->field === 'ended_at');
                $ultima = $correcciones->last();

                fputcsv($salida, [
                    $jornada->work_date->format('Y-m-d'),
                    $primero?->started_at ? Tiempo::hora($primero->started_at) : '',
                    $jornada->estaCerrada() && $ultimo?->ended_at ? Tiempo::hora($ultimo->ended_at) : '',
                    $inicioAnterior ? Tiempo::hora($inicioAnterior->previous_value) : '',
                    $finAnterior ? Tiempo::hora($finAnterior->previous_value) : '',
                    $ultima?->autor?->name ?? '',
                    $ultima ? $ultima->created_at->format('Y-m-d H:i') : '',
                    $primero?->anotado_at ? Tiempo::hora($primero->anotado_at) : '',
                    $trabajo->contains(fn (Tramo $tramo) => $tramo->fuera_del_equipo) ? 'sí' : '',
                    $pausas->map(function (Tramo $tramo) {
                        $hasta = $tramo->ended_at ? Tiempo::hora($tramo->ended_at) : 'abierta';

                        return Tiempo::hora($tramo->started_at).'–'.$hasta;
                    })->implode(' | '),
                    Tiempo::texto($this->jornada->minutosDeJornada($jornada)),
                    $porEncima ? Tiempo::texto($porEncima) : '',
                ], ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function pedirMotivo(Request $request, User $persona): ?View
    {
        if ($request->user()->id === $persona->id || $request->session()->get('consulta.'.$persona->id)) {
            return null;
        }

        $request->session()->put('url.intended', url()->full());

        return view('registro.motivo', [
            'persona' => $persona,
            'esResponsable' => true,
        ]);
    }

    private function autorizar(User $visor, User $persona): void
    {
        if ($visor->id !== $persona->id && ! $visor->esResponsable()) {
            abort(403);
        }
    }
}
