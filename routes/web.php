<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\JornadaController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware(['auth', 'activo'])->group(function () {
    Route::get('/', [JornadaController::class, 'index'])->name('jornada');
    Route::post('/jornada/entrada', [JornadaController::class, 'entrar'])->name('jornada.entrada');
    Route::post('/jornada/salida', [JornadaController::class, 'salir'])->name('jornada.salida');
    Route::post('/jornada/pausa', [JornadaController::class, 'pausar'])->name('jornada.pausa');
    Route::post('/jornada/volver', [JornadaController::class, 'volver'])->name('jornada.volver');
    Route::post('/jornada/ya-estaba', [JornadaController::class, 'yaEstaba'])->name('jornada.ya');
    Route::post('/jornada/reunion', [JornadaController::class, 'reunion'])->name('jornada.reunion');
    Route::post('/jornada/cerrar', [JornadaController::class, 'cerrar'])->name('jornada.cerrar');
    Route::post('/jornada/seguir', [JornadaController::class, 'seguir'])->name('jornada.seguir');
    Route::post('/jornada/fuera', [JornadaController::class, 'fuera'])->name('jornada.fuera');
    Route::post('/jornada/festivo', [JornadaController::class, 'festivo'])->name('jornada.festivo');
    Route::post('/jornada/completar', [JornadaController::class, 'completar'])->name('jornada.completar');
    Route::post('/jornada/borrar-prueba', [JornadaController::class, 'borrarPrueba'])->name('jornada.borrar-prueba');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/registro', [RegistroController::class, 'index'])->name('registro');
    Route::get('/registro/copia.csv', [RegistroController::class, 'csv'])->name('registro.csv');
    Route::post('/registro/explicacion', [RegistroController::class, 'explicar'])->name('registro.explicar');
    Route::get('/registro/{jornada}', [RegistroController::class, 'show'])->name('registro.show');
    Route::post('/tramos/{tramo}/corregir', [RegistroController::class, 'corregir'])->name('tramos.corregir');

    Route::middleware('responsable')->group(function () {
        Route::get('/equipo', [EquipoController::class, 'index'])->name('equipo.index');
        Route::get('/equipo/nueva', [EquipoController::class, 'create'])->name('equipo.create');
        Route::post('/equipo', [EquipoController::class, 'store'])->name('equipo.store');
        Route::get('/equipo/{user}', [EquipoController::class, 'show'])->name('equipo.show');
        Route::post('/equipo/{user}/horario', [EquipoController::class, 'horario'])->name('equipo.horario');
        Route::post('/equipo/{user}/ausencia', [EquipoController::class, 'ausencia'])->name('equipo.ausencia');
        Route::post('/equipo/{user}/baja', [EquipoController::class, 'baja'])->name('equipo.baja');
        Route::get('/equipo/{user}/registro', [RegistroController::class, 'de'])->name('equipo.registro');
        Route::post('/equipo/{user}/registro', [RegistroController::class, 'motivo'])->name('equipo.motivo');
        Route::get('/equipo/{user}/copia.csv', [RegistroController::class, 'csvDe'])->name('equipo.csv');

        Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario');
        Route::post('/calendario', [CalendarioController::class, 'store'])->name('calendario.store');
        Route::post('/calendario/fallo', [CalendarioController::class, 'fallo'])->name('calendario.fallo');
    });
});
