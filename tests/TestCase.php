<?php

namespace Tests;

use App\Models\Centro;
use App\Models\Festivo;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /** @var list<array<string, mixed>> */
    protected array $festivosNager = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function ($request) {
            if (! str_contains($request->url(), 'date.nager.at')) {
                return Http::response([], 404);
            }

            return Http::response($this->festivosNager);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $dias
     */
    protected function festivosPublicos(array $dias): void
    {
        $this->festivosNager = $dias;
        foreach ($dias as $dia) {
            if (($dia['types'] ?? []) === ['Observance']) {
                continue;
            }

            $nombresSede = [
                'ES-MD' => 'Madrid',
                'ES-CT' => 'Barcelona',
                'ES-AN' => 'Sevilla',
                'ES-GETAFE' => 'Getafe',
            ];
            $centro = ($dia['global'] ?? false)
                ? null
                : Centro::query()->where('name', $nombresSede[$dia['counties'][0] ?? ''] ?? ($dia['counties'][0] ?? ''))->first();

            Festivo::create([
                'holiday_date' => $dia['date'],
                'municipality' => $centro?->municipality ?? '',
                'name' => $dia['localName'],
                'ambito' => ($dia['global'] ?? false) ? 'estado' : 'comunidad',
                'community' => $centro?->community,
                'centro_id' => ($dia['global'] ?? false) ? null : $centro?->id,
            ]);
        }
        cache()->flush();
    }
}
