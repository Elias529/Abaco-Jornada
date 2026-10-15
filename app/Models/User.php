<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'email', 'password', 'role', 'active', 'starts_on', 'ends_on', 'municipality', 'centro'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Gestiona el equipo y, además, hace las funciones de dirección: estado de
     * todas las personas, resumen del mes y consulta de registros con motivo.
     */
    public function esJefa(): bool
    {
        return $this->role === 'jefa';
    }

    /**
     * El administrador técnico ficha como cualquier persona del equipo y, además,
     * gestiona las actualizaciones y comprueba la base de datos activa.
     */
    public function esTecnico(): bool
    {
        return $this->role === 'tecnico';
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function jornadas(): HasMany
    {
        return $this->hasMany(Jornada::class);
    }

    public function ausencias(): HasMany
    {
        return $this->hasMany(Ausencia::class);
    }

    public function explicaciones(): HasMany
    {
        return $this->hasMany(Explicacion::class);
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }

    public function horarioEn(Carbon $fecha): ?Horario
    {
        return $this->horarios()
            ->whereDate('effective_from', '<=', $fecha->toDateString())
            ->orderByDesc('effective_from')
            ->first();
    }

    public function estaEnPlantillaEl(Carbon $fecha): bool
    {
        $dia = $fecha->toDateString();
        if (! $this->starts_on || $dia < $this->starts_on->toDateString()) {
            return false;
        }

        return ! $this->ends_on || $dia <= $this->ends_on->toDateString();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }
}
