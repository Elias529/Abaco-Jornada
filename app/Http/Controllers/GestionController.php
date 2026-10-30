<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use App\Models\Festivo;
use App\Models\SolicitudVacaciones;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GestionController extends Controller
{
    public function index(Request $request): View
    {
        $mostrarTodasVacaciones = $request->boolean('todas_vacaciones');
        $solicitudesVacacionesPendientes = SolicitudVacaciones::query()
            ->where('estado', SolicitudVacaciones::PENDIENTE)
            ->count();
        $limiteSolicitudesVacaciones = $mostrarTodasVacaciones ? 100 : 3;

        return view('gestion.index', [
            'solicitudesVacaciones' => SolicitudVacaciones::query()
                ->with('autor')
                ->where('estado', SolicitudVacaciones::PENDIENTE)
                ->latest('id')
                ->limit($limiteSolicitudesVacaciones)
                ->get(),
            'solicitudesVacacionesPendientes' => $solicitudesVacacionesPendientes,
            'mostrarTodasVacaciones' => $mostrarTodasVacaciones,
            'hayMasSolicitudesVacaciones' => $mostrarTodasVacaciones || $solicitudesVacacionesPendientes > 3,
            'centros' => Centro::query()->orderBy('name')->get(),
            'festivosGestion' => Festivo::query()->with('centro')->orderByDesc('holiday_date')->get(),
        ]);
    }
}
