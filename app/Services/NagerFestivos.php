<?php

namespace App\Services;

use App\Support\DiaFestivo;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NagerFestivos
{
    /**
     * Festivos públicos de Nager.Date que aplican a un centro.
     * Un centro como ES-MD usa el país ES y se queda con los días
     * de todo el país y con los que nombran ese centro.
     *
     * @return Collection<int, DiaFestivo>
     */
    public function delAnio(string $centro, int $anio): Collection
    {
        $pais = $this->pais($centro);
        if ($pais === null) {
            return collect();
        }

        $lista = $this->descargar($pais, $anio);
        if ($lista === null) {
            return collect();
        }

        return $this->trasladarDomingos(collect($lista)
            ->filter(fn ($dia) => is_array($dia) && $this->aplica($dia, $centro))
            ->map(fn (array $dia) => new DiaFestivo(
                Carbon::parse($dia['date'])->timezone(config('app.timezone'))->startOfDay(),
                (string) ($dia['localName'] ?? $dia['name'] ?? 'Festivo'),
                ($dia['global'] ?? false) ? 'estado' : 'comunidad',
            )));
    }

    /**
     * En España, el festivo que cae en domingo se celebra el lunes.
     * Si ese lunes ya es festivo, pasa al siguiente día libre.
     *
     * @param  Collection<int, DiaFestivo>  $dias
     * @return Collection<int, DiaFestivo>
     */
    private function trasladarDomingos(Collection $dias): Collection
    {
        $dias = $dias->sortBy(fn (DiaFestivo $dia) => $dia->holiday_date->toDateString())->values();
        $ocupados = [];
        foreach ($dias as $dia) {
            $ocupados[$dia->holiday_date->toDateString()] = true;
        }

        return $dias->map(function (DiaFestivo $dia) use (&$ocupados) {
            $fecha = $dia->holiday_date->copy();
            if ($fecha->dayOfWeekIso !== 7) {
                return $dia;
            }

            unset($ocupados[$fecha->toDateString()]);
            $fecha = $fecha->addDay();
            while (isset($ocupados[$fecha->toDateString()])) {
                $fecha = $fecha->addDay();
            }
            $ocupados[$fecha->toDateString()] = true;

            return new DiaFestivo($fecha, $dia->name, $dia->ambito);
        })->sortBy(fn (DiaFestivo $dia) => $dia->holiday_date->toDateString())->values();
    }

    public function es(string $centro, Carbon $fecha): bool
    {
        $clave = $fecha->toDateString();

        return $this->delAnio($centro, $fecha->year)
            ->contains(fn (DiaFestivo $dia) => $dia->holiday_date->toDateString() === $clave);
    }

    private function pais(string $centro): ?string
    {
        if (! preg_match('/^([A-Za-z]{2})(?:-[A-Za-z0-9]+)?$/', $centro, $partes)) {
            return null;
        }

        return strtoupper($partes[1]);
    }

    /**
     * @param  array<string, mixed>  $dia
     */
    private function aplica(array $dia, string $centro): bool
    {
        $tipos = $dia['types'] ?? [];
        if (! is_array($tipos) || ! in_array('Public', $tipos, true)) {
            return false;
        }

        if (($dia['global'] ?? false) === true) {
            return true;
        }

        $condados = $dia['counties'] ?? null;

        return is_array($condados) && in_array($centro, $condados, true);
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function descargar(string $pais, int $anio): ?array
    {
        $clave = "nager.festivos.{$pais}.{$anio}";
        $guardado = Cache::get($clave);
        if (is_array($guardado)) {
            return $guardado;
        }

        try {
            $peticion = Http::timeout(8)->acceptJson();
            $certificado = base_path('storage/certs/cacert.pem');
            if (is_file($certificado)) {
                $peticion = $peticion->withOptions(['verify' => $certificado]);
            }

            $respuesta = $peticion->get("https://date.nager.at/api/v3/PublicHolidays/{$anio}/{$pais}");
        } catch (ConnectionException) {
            return null;
        }

        $datos = $respuesta->json();
        if (! $respuesta->successful() || ! is_array($datos) || ! array_is_list($datos)) {
            return null;
        }

        Cache::put($clave, $datos, now()->addDay());

        return $datos;
    }
}
