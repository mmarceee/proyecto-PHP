<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesional;

class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $profesional = $user?->profesional;
        $estadoProfesional = $profesional?->estado;

        $esAdmin = $user?->esAdmin();
        $esProfesional = !$esAdmin && $user?->esProfesionalAprobado();
        $esCliente = !$esAdmin && !$esProfesional;

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
            $profesionalesPendientes = Profesional::with('user')
                ->where('estado', 'pendiente')
                ->latest()
                ->get()
                ->map(function ($profesional) {
                    return [
                        'id' => $profesional->id,
                        'user_id' => $profesional->user_id,
                        'name' => $profesional->user?->name ?? 'Usuario sin nombre',
                        'email' => $profesional->user?->email,
                        'specialty' => $profesional->especialidad,
                        'commercial_name' => $profesional->nombre_comercial,
                        'status' => $profesional->estado,
                        'date' => $profesional->created_at?->diffForHumans(),
                    ];
                });
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
            ],

            'datos' => [
                'profesionalesPendientes' => $profesionalesPendientes,
                'consultasHoy' => [],
                'proximasSesiones' => [],
            ],
        ]);
    }
}