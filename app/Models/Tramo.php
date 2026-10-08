<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tramo extends Model
{
    public const TRABAJO = 'trabajo';

    public const PAUSA = 'pausa';

    protected $fillable = [
        'jornada_id',
        'tipo',
        'started_at',
        'ended_at',
        'anotado_at',
        'fuera_del_equipo',
        'situacion',
    ];

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class);
    }

    public function correcciones(): HasMany
    {
        return $this->hasMany(Correccion::class)->orderBy('created_at');
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'anotado_at' => 'datetime',
            'fuera_del_equipo' => 'boolean',
        ];
    }
}
