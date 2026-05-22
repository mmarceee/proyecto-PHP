<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesional;
use App\Services\AgendaService;

class BusquedaApiController extends Controller
{
    /**
     * Búsqueda asincrónica de profesionales y sus servicios
     */
    public function buscar(Request $request)
    {
        $queryText = $request->query('q');
        $categoria = $request->query('categoria');

        $query = Profesional::with(['user', 'servicios'])
            ->where('estado', 'aprobado');

        if ($queryText) {
            $query->whereHas('user', function($q) use ($queryText) {
                $q->where('name', 'like', "%{$queryText}%");
            })->orWhereHas('servicios', function($q) use ($queryText) {
                $q->where('nombre', 'like', "%{$queryText}%")
                  ->orWhere('descripcion', 'like', "%{$queryText}%");
            });
        }

        // Filtro opcional por categoría si tuvieras la relación
        if ($categoria && $categoria !== 'Todas las categorías') {
            $query->whereHas('servicios', function($q) use ($categoria) {
                $q->where('categoria', $categoria);
            });
        }

        $profesionales = $query->get()->map(function($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->user->name,
                'nombre_comercial' => $p->nombre_comercial,
                'servicios' => $p->servicios->map(function($s) {
                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'precio' => $s->precio ?? 0,
                        'modalidad' => $s->modalidad ?? 'Virtual', // Dinámico por servicio
                    ];
                })
            ];
        });

        return response()->json(['profesionales' => $profesionales]);
    }

    /**
     * Obtener la agenda de un profesional específico
     */
    public function obtenerAgendaProfesional(Request $request, $id, AgendaService $agendaService)
    {
        $profesional = Profesional::findOrFail($id);
        $fechaInicio = $request->query('fecha');

        $semana = $agendaService->obtenerAgendaSemana($profesional, $fechaInicio);

        return response()->json(['semana' => $semana]);
    }
}