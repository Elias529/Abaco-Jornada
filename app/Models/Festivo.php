<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festivo extends Model
{
    protected $fillable = [
        'holiday_date',
        'municipality',
        'name',
        'ambito',
    ];

    public function etiqueta(): string
    {
        return match ($this->ambito) {
            'estado' => 'Festivo Nacional',
            'comunidad' => 'Festivo Autonómico',
            'pueblo' => 'Festivo Local',
            default => $this->municipality === '' ? 'Todos los centros' : 'Festivo Local',
        };
    }

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }
}
