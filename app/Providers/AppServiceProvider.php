<?php

namespace App\Providers;

use App\Models\Incidencia;
use App\Models\SolicitudVacaciones;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

        if (! $this->app->runningInConsole()) {
            URL::forceRootUrl($this->app['request']->getSchemeAndHttpHost());
        }

        View::composer('layouts.app', function ($view): void {
            $usuario = Auth::user();
            $view->with([
                'ticketsNuevos' => $usuario && $usuario->esTecnico()
                    ? Ticket::query()->where('estado', Ticket::ABIERTO)->count()
                    : 0,
                'incidenciasNuevas' => $usuario && $usuario->esJefa()
                    ? Incidencia::query()->whereNull('vista_at')->count()
                    : 0,
                'solicitudesNuevas' => $usuario && $usuario->esJefa()
                    ? SolicitudVacaciones::query()->where('estado', SolicitudVacaciones::PENDIENTE)->count()
                    : 0,
            ]);
        });
    }
}
