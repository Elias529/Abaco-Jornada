<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festivo extends Model
{
    protected $fillable = [
        'holiday_date',
        'municipality',
        'name',
    ];

    public function ambito(): string
    {
        return $this->municipality === '' ? 'Todos los centros' : $this->municipality;
    }

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }
}
