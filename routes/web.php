<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\JefeController;
use App\Http\Controllers\JornadaController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware(['auth', 'activo'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

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
    Route::get('/jornada/incidencia', [JornadaController::class, 'crearIncidencia'])->name('jornada.incidencia.crear');
    Route::post('/jornada/incidencia', [JornadaController::class, 'incidencia'])->name('jornada.incidencia');
    Route::post('/jornada/festivo', [JornadaController::class, 'festivo'])->name('jornada.festivo');
    Route::post('/jornada/completar', [JornadaController::class, 'completar'])->name('jornada.completar');
    Route::post('/jornada/borrar-prueba', [JornadaController::class, 'borrarPrueba'])->name('jornada.borrar-prueba');

    Route::get('/registro', [RegistroController::class, 'index'])->name('registro');
    Route::get('/registro/copia.csv', [RegistroController::class, 'csv'])->name('registro.csv');
    Route::post('/registro/explicacion', [RegistroController::class, 'explicar'])->name('registro.explicar');
    Route::get('/registro/{jornada}', [RegistroController::class, 'show'])->name('registro.show');
    Route::post('/tramos/{tramo}/corregir', [RegistroController::class, 'corregir'])->name('tramos.corregir');

    // El calendario lo ve toda la plantilla. Los festivos salen de Nager.Date, filtrados por el centro de quien mira.
    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario');

    // Tickets: cualquier persona cuenta un problema; solo ella y el técnico lo ven.
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/nuevo', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/mensajes', [TicketController::class, 'mensaje'])->middleware('throttle:20,1')->name('tickets.mensaje');

    Route::middleware('jefa')->group(function () {
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

        Route::post('/calendario/fallo', [CalendarioController::class, 'fallo'])->name('calendario.fallo');

        // Funciones de dirección: estado del equipo y registros en solo lectura.
        // Lo único que se escribe es el motivo de cada consulta, que queda anotado.
        Route::prefix('jefe')->name('jefe.')->group(function () {
            Route::get('/', [JefeController::class, 'index'])->name('index');
            Route::get('/consultas', [JefeController::class, 'consultas'])->name('consultas');
            Route::get('/personas/{user}', [JefeController::class, 'persona'])->name('persona');
            Route::get('/personas/{user}/registro', [JefeController::class, 'registro'])->name('registro');
            Route::post('/personas/{user}/registro', [JefeController::class, 'motivo'])->name('motivo');
            Route::get('/personas/{user}/copia.csv', [JefeController::class, 'csv'])->name('csv');
            Route::get('/dias/{jornada}', [JefeController::class, 'dia'])->name('dia');
        });
    });

    // Administrador técnico: estado de la base de datos, copias y actualizaciones.
    // No ve registros de otras personas.
    Route::middleware('tecnico')->prefix('tecnico')->name('tecnico.')->group(function () {
        Route::get('/', [TecnicoController::class, 'index'])->name('index');
        Route::post('/copia', [TecnicoController::class, 'copia'])->name('copia');
        Route::post('/actualizar', [TecnicoController::class, 'actualizar'])->name('actualizar');
        Route::get('/tickets', [TecnicoController::class, 'tickets'])->name('tickets');
        Route::post('/tickets/{ticket}/estado', [TicketController::class, 'estado'])->name('tickets.estado');
    });
});
