<?php

namespace App\Services;

use App\Models\Centro;
use App\Models\Festivo;
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
    public function delAnio(Centro|string|null $centro, int $anio): Collection
    {
        $centro = $this->resolverCentro($centro);

        return Festivo::query()
            ->whereYear('holiday_date', $anio)
            ->where(function ($consulta) use ($centro) {
                $consulta->where('ambito', 'estado')
                    ->orWhere(function ($consulta) use ($centro) {
                        $consulta->where('ambito', 'comunidad')
                            ->where('community', $centro?->community);
                    })
                    ->orWhere(function ($consulta) use ($centro) {
                        $consulta->where('ambito', 'pueblo')
                            ->where('municipality', $centro?->municipality ?? '');
                    })
                    ->orWhere(function ($consulta) use ($centro) {
                        $consulta->whereNull('ambito')
                            ->where('municipality', $centro?->municipality ?? '');
                    })
                    ->orWhere(function ($consulta) {
                        $consulta->whereNull('ambito')
                            ->where('municipality', '');
                    });
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

    public function es(Centro|string|null $centro, Carbon $fecha): bool
    {
        $clave = $fecha->toDateString();

        return $this->delAnio($centro, $fecha->year)
            ->contains(fn (DiaFestivo $dia) => $dia->holiday_date->toDateString() === $clave);
    }

    private function resolverCentro(Centro|string|null $centro): ?Centro
    {
        if ($centro instanceof Centro) {
            return $centro;
        }

        if ($centro === null || $centro === '') {
            return null;
        }

        return Centro::query()
            ->where('name', $centro)
            ->orWhereKey($centro)
            ->first();
    }
}
