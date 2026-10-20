<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un aviso que una persona envía a la jefa. No declara horas.
 */
class Incidencia extends Model
{
    public const ABIERTA = 'abierta';

    public const RESUELTA = 'resuelta';

    protected $fillable = [
        'user_id',
        'cuerpo',
        'estado',
        'resuelta_at',
        'vista_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resuelta_at' => 'datetime',
            'vista_at' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(IncidenciaMensaje::class)->oldest('id');
    }

    public function estaResuelta(): bool
    {
        return $this->estado === self::RESUELTA;
    }

    public function estadoTexto(): string
    {
        return $this->estaResuelta() ? 'Resuelta' : 'Abierta';
    }
}
