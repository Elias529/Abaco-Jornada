<?php

namespace Tests;

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
        cache()->flush();
    }
}
