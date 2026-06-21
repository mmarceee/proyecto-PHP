<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProfesionalService;
use App\Services\DashboardService;

class DashboardApiController extends Controller
{
    public function index(Request $request, ProfesionalService $profesionalService, DashboardService $DashboardService)
    {
        $user = $request->user();

        $profesional = $user?->profesional;
        $estadoProfesional = $profesional?->estado;

        $esAdmin = $user?->esAdmin();
        $esProfesional = !$esAdmin && $user?->esProfesionalAprobado();

        $hora = now()->hour;

        if ($hora < 12) {
            $saludo = 'Buenos días';
        } elseif ($hora < 20) {
            $saludo = 'Buenas tardes';
        } else {
            $saludo = 'Buenas noches';
        }

        if ($esAdmin) {
            $tipo = 'admin';
        } elseif ($esProfesional) {
            $tipo = 'profesional';
        } else {
            $tipo = 'cliente';
        }

        $profesionalesPendientes = [];

        if ($esAdmin) {
            $profesionalesPendientes = $profesionalService->obtenerPendientes();
        }

        $consultasHoy = [];
        $proximasSesiones = [];
        $reservasPendientes = [];
        $resenasRecibidas = [];


        if ($esProfesional && $profesional) {
            $consultasHoy = $DashboardService->obtenerConsultasHoy($profesional->id);
            $reservasPendientes = $DashboardService->obtenerReservasPendientesProfesional($profesional->id);
            $resenasRecibidas = $DashboardService->obtenerResenasRecibidas($profesional->id);
        }

        if (!$esAdmin) {
            $proximasSesiones = $DashboardService->obtenerProximasSesiones($user->id);
        }

        return response()->json([
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
            ],
            'saludo' => $saludo,
            'tipo' => $tipo,
            'profesional' => [
                'tieneSolicitud' => $profesional !== null,
                'estado' => $estadoProfesional,
                'pendiente' => $estadoProfesional === 'pendiente',
                'aprobado' => $estadoProfesional === 'aprobado',
                'reputacion_promedio' => $profesional ? $profesional->reputacion_promedio : 0.00,
            ],
            'datos' => [
                'profesionalesPendientes' => $profesionalesPendientes,
                'consultasHoy' => $consultasHoy,
                'proximasSesiones' => $proximasSesiones,
                'reservasPendientes' => $reservasPendientes,
                'resenasRecibidas' => $resenasRecibidas,
            ],
        ]);
    }

    public function obtenerCalendarioConsultas(Request $request, DashboardService $dashboardService)
    {
        $user = $request->user();
        if (!$user->esProfesionalAprobado()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $proximasSesiones = $dashboardService->obtenerProximasSesionesProfesional($user->profesional->id);
        
        return response()->json([
            'proximasSesiones' => $proximasSesiones
        ]);
    }
}