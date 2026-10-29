<?php

namespace App\Services;

use App\Models\Festivo;
use App\Sede;
use App\Support\DiaFestivo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NagerFestivos
{
    /**
     * Festivos locales cargados en la base de datos que aplican a un centro.
     *
     * @return Collection<int, DiaFestivo>
     */
    public function delAnio(string $centro, int $anio): Collection
    {
        $municipio = Sede::tryFrom($centro)?->municipio() ?? $centro;

        return Festivo::query()
            ->whereYear('holiday_date', $anio)
            ->where(function ($consulta) use ($municipio) {
                $consulta->where('municipality', '')
                    ->orWhere('municipality', $municipio);
            })
            ->orderBy('holiday_date')
            ->get()
            ->map(fn (Festivo $festivo) => new DiaFestivo(
                $festivo->holiday_date->copy()->startOfDay(),
                $festivo->name,
                $festivo->ambito ?: 'pueblo',
            ))
            ->values();
    }

    public function es(string $centro, Carbon $fecha): bool
    {
        $clave = $fecha->toDateString();

        return $this->delAnio($centro, $fecha->year)
            ->contains(fn (DiaFestivo $dia) => $dia->holiday_date->toDateString() === $clave);
    }
}
