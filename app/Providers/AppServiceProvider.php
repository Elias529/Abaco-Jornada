<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // La dirección no es una persona del equipo: ninguna ruta de gestión
        // ({user}) puede cambiar su acceso, su horario ni sus ausencias.
        Route::bind('user', fn (string $valor) => User::query()
            ->where('role', '!=', 'jefe')
            ->findOrFail($valor));
    }
}
