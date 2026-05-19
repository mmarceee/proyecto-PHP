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

                        'name' => trim(
                            ($profesional->user?->name ?? '') . ' ' . ($profesional->user?->apellido ?? '')
                        ),

                        'email' => $profesional->user?->email,
                        'phone' => $profesional->user?->telefono,

                        'specialty' => $profesional->especialidad,
                        'commercial_name' => $profesional->nombre_comercial,
                        'description' => $profesional->descripcion,

                        'status' => $profesional->estado,
                        'date' => $profesional->created_at?->diffForHumans(),
                    ];
                });
        }

        $consultasHoy = [];
        $proximasSesiones = [];

        //SIGUEN LOS DATOS HARCODEADOS HASTA QUE SE IMPLEMENTE LA LÓGICA REAL PARA OBTENER LAS CONSULTAS Y SESIONES DE LOS PROFESIONALES Y CLIENTES

        if ($esProfesional) {
            $consultasHoy = [
                [
                    'id' => 1,
                    'time' => '10:00',
                    'period' => 'AM',
                    'client_name' => 'Maria G.',
                    'reason' => 'Consulta inicial',
                    'status' => 'Por comenzar',
                    'action_label' => 'Enlace',
                    'packages' => [
                        ['name' => 'Flujo Premium', 'used' => 2, 'total' => 8],
                        ['name' => 'Plan Base', 'used' => 1, 'total' => 5],
                        ['name' => 'Consulta Inicial', 'used' => 1, 'total' => 1],
                    ],
                ],
                [
                    'id' => 2,
                    'time' => '12:30',
                    'period' => 'PM',
                    'client_name' => 'Carlos T.',
                    'reason' => 'Seguimiento',
                    'status' => 'Confirmada',
                    'action_label' => 'Detalles',
                    'packages' => [
                        ['name' => 'Seguimiento Mensual', 'used' => 5, 'total' => 5],
                        ['name' => 'Plan Base', 'used' => 3, 'total' => 5],
                        ['name' => 'Asesoría Técnica', 'used' => 2, 'total' => 4],
                    ],
                ],
            ];
        }

        if ($esCliente) {
            $proximasSesiones = [
                [
                    'id' => 1,
                    'date_label' => 'Mañana',
                    'time' => '15:00',
                    'professional_name' => 'Ana Rodríguez',
                    'specialty' => 'Asesoría legal',
                    'status' => 'Confirmada',
                    'packages' => [
                        ['name' => 'Pack Consulta Legal', 'used' => 1, 'total' => 4],
                        ['name' => 'Seguimiento Jurídico', 'used' => 0, 'total' => 3],
                    ],
                ],
                [
                    'id' => 2,
                    'date_label' => 'Viernes',
                    'time' => '09:30',
                    'professional_name' => 'Martín Silva',
                    'specialty' => 'Técnico electricista',
                    'status' => 'Pendiente',
                    'packages' => [],
                ],
            ];
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
                'consultasHoy' => $consultasHoy,
                'proximasSesiones' => $proximasSesiones,
            ],
        ]);
    }
}