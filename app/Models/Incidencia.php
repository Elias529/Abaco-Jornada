<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un aviso que una persona envía a la jefa. No declara horas.
 */
class Incidencia extends Model
{
    protected $fillable = [
        'user_id',
        'cuerpo',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
