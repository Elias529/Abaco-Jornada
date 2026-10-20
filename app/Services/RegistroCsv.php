<?php

namespace App\Services;

use App\Models\Jornada;
use App\Models\Tramo;
use App\Models\User;
use App\Support\Tiempo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Copia del registro de una persona en CSV. La comparten quien consulta
 * su propio registro y quien lo consulta con motivo.
 */
class RegistroCsv
{
    /** @var list<string> */
    public const COLUMNAS = ['Fecha', 'Inicio', 'Fin', 'Total', 'Estado', 'Incidencia'];

    public function __construct(private JornadaService $jornada) {}

    /**
     * La misma fila que pinta la tabla de Mi registro.
     *
     * @return array{Fecha: string, Inicio: string, Fin: string, Total: string, Estado: string, Incidencia: string}
     */
    public function fila(User $persona, Jornada $jornada): array
    {
        $jornada->loadMissing('tramos');
        $primero = $jornada->tramos
            ->where('tipo', Tramo::TRABAJO)
            ->sortBy('started_at')
            ->first();
        $ultimo = $jornada->tramos
            ->filter(fn (Tramo $tramo) => $tramo->ended_at !== null)
            ->sortBy('ended_at')
            ->last();
        $incidencia = $jornada->tramos->contains(fn (Tramo $tramo) => $tramo->situacion === 'incidencia');

        return [
            'Fecha' => Tiempo::fecha($jornada->work_date),
            'Inicio' => $primero?->started_at ? Tiempo::hora($primero->started_at) : '',
            'Fin' => $jornada->estaCerrada() && $ultimo?->ended_at ? Tiempo::hora($ultimo->ended_at) : '',
            'Total' => Tiempo::texto($this->jornada->minutosRegistrados($persona, $jornada)),
            'Estado' => $this->estado($jornada),
            'Incidencia' => $incidencia ? 'Sí' : 'No',
        ];
    }

    /**
     * Datos de una fila de la lista de Mi registro.
     *
     * @return array{fecha: string, tramos: string, barras: list<array{left: float, width: float}>, total: string, estado: string, clase: string, incidencia: bool}
     */
    public function presentacion(User $persona, Jornada $jornada): array
    {
        $jornada->loadMissing('tramos');
        $fecha = $jornada->work_date->copy()->locale('es');
        $trabajos = $jornada->tramos->where('tipo', Tramo::TRABAJO)->sortBy('started_at')->values();
        $textos = [];
        $barras = [];
        foreach ($trabajos as $tramo) {
            $fin = $tramo->ended_at ?? now();
            $textos[] = Tiempo::hora($tramo->started_at).' – '.Tiempo::hora($fin);
            $trozo = $this->eje($tramo->started_at, $fin);
            if ($trozo) {
                $barras[] = $trozo;
            }
        }
        $abierta = $jornada->tramoAbierto() !== null;

        return [
            'fecha' => ucfirst($fecha->isoFormat('dddd')).', '.$fecha->day.' '.rtrim($fecha->isoFormat('MMM'), '.'),
            'tramos' => $textos === [] ? '—' : implode(' · ', $textos),
            'barras' => $barras,
            'total' => Tiempo::corto($this->jornada->minutosRegistrados($persona, $jornada)),
            'estado' => $abierta ? 'Abierta' : ($jornada->closed_late ? 'Cierre al día siguiente' : 'Cerrada'),
            'clase' => $abierta ? 'abierta' : ($jornada->closed_late ? 'tardia' : 'cerrada'),
            'incidencia' => $jornada->tramos->contains(fn (Tramo $tramo) => $tramo->situacion === 'incidencia'),
        ];
    }

    /**
     * @return Builder<Jornada>
     */
    public function consulta(User $persona, string $desde, string $hasta, string $estado, string $incidencia): Builder
    {
        return Jornada::query()
            ->with('tramos')
            ->where('user_id', $persona->id)
            ->when($desde !== '', fn ($q) => $q->whereDate('work_date', '>=', $desde))
            ->when($hasta !== '', fn ($q) => $q->whereDate('work_date', '<=', $hasta))
            ->when($estado === 'pendiente', fn ($q) => $q->whereHas('tramos', fn ($tramo) => $tramo->whereNull('ended_at')))
            ->when($estado === 'tardia', fn ($q) => $q
                ->where('closed_late', true)
                ->whereDoesntHave('tramos', fn ($tramo) => $tramo->whereNull('ended_at')))
            ->when($estado === 'cerrada', fn ($q) => $q
                ->where('closed_late', false)
                ->whereDoesntHave('tramos', fn ($tramo) => $tramo->whereNull('ended_at')))
            ->when($incidencia === 'si', fn ($q) => $q->whereHas('tramos', fn ($tramo) => $tramo->where('situacion', 'incidencia')))
            ->when($incidencia === 'no', fn ($q) => $q->whereDoesntHave('tramos', fn ($tramo) => $tramo->where('situacion', 'incidencia')))
            ->orderByDesc('work_date');
    }

    public function descargar(User $persona, ?string $desde = null, ?string $hasta = null, string $estado = '', string $incidencia = ''): StreamedResponse
    {
        $desde = is_string($desde) ? $desde : '';
        $hasta = is_string($hasta) ? $hasta : '';
        $jornadas = $this->consulta($persona, $desde, $hasta, $estado, $incidencia)->get();

        return $this->archivo($persona, $jornadas);
    }

    public function descargarVacio(User $persona): StreamedResponse
    {
        return $this->archivo($persona, collect());
    }

    private function archivo(User $persona, iterable $jornadas): StreamedResponse
    {
        $nombre = 'registro-'.$persona->id.'.csv';

        return response()->streamDownload(function () use ($jornadas, $persona) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, self::COLUMNAS, ';');

            foreach ($jornadas as $jornada) {
                fputcsv($salida, array_values($this->fila($persona, $jornada)), ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function estado(Jornada $jornada): string
    {
        if ($jornada->tramoAbierto()) {
            return 'Pendiente de cerrar';
        }

        if ($jornada->closed_late) {
            return 'Cerrada al día siguiente';
        }

        return 'Cerrada';
    }

    /**
     * @return array{left: float, width: float}|null
     */
    private function eje(Carbon $inicio, Carbon $fin): ?array
    {
        $desde = 7 * 60;
        $hasta = 21 * 60;
        $a = max($desde, min($hasta, $this->minuto($inicio)));
        $b = max($desde, min($hasta, $this->minuto($fin)));
        if ($b <= $a) {
            return null;
        }

        return [
            'left' => round(($a - $desde) / ($hasta - $desde) * 100, 2),
            'width' => round(($b - $a) / ($hasta - $desde) * 100, 2),
        ];
    }

    private function minuto(Carbon $momento): int
    {
        $local = $momento->timezone(config('app.timezone'));

        return ((int) $local->format('H')) * 60 + (int) $local->format('i');
    }
}
