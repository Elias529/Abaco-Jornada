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

#[Fillable(['name', 'email', 'password', 'role', 'active', 'starts_on', 'municipality'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function esResponsable(): bool
    {
        return $this->role === 'responsable';
    }

    /**
     * La dirección consulta los registros de todas las personas y no los cambia.
     * No ficha: el personal de alta dirección queda fuera del registro de jornada.
     */
    public function esJefe(): bool
    {
        return $this->role === 'jefe';
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

    public function horarioEn(Carbon $fecha): ?Horario
    {
        return $this->horarios()
            ->whereDate('effective_from', '<=', $fecha->toDateString())
            ->orderByDesc('effective_from')
            ->first();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'starts_on' => 'date',
        ];
    }
}
