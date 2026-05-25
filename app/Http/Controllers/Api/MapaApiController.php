<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LugarAtencion;
use Illuminate\Http\JsonResponse;

class MapaApiController extends Controller
{
    /**
     * Retorna todos los lugares de atención con las coordenadas 
     * y los datos básicos del profesional asociado.
     */
    public function index(): JsonResponse
    {
        // Traemos las coordenadas y la relación completa (sin restringir columnas
        // para evitar el error de Eloquent con las tablas separadas)
        $lugares = LugarAtencion::with(['profesional.user'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        // Mapeamos los datos para enviar al frontend un JSON limpio y fácil de leer
        $lugaresFormateados = $lugares->map(function ($lugar) {
            
            // Extraemos los datos dependiendo de tu estructura de base de datos.
            // (Si el nombre está directo en el profesional, o en la relación de usuario)
            $nombreProfesional = $lugar->profesional->usuario->nombre ?? $lugar->profesional->nombre ?? 'Profesional';
            $apellidoProfesional = $lugar->profesional->usuario->apellido ?? $lugar->profesional->apellido ?? '';
            $nombreComercial = $lugar->profesional->nombre_comercial ?? null;
            
            return [
                'id' => $lugar->id,
                'profesional_id' => $lugar->profesional_id,
                'nombre' => $lugar->nombre,
                'direccion' => $lugar->direccion,
                'ciudad' => $lugar->ciudad,
                'latitud' => $lugar->latitud,
                'longitud' => $lugar->longitud,
                'profesional' => [
                    'nombre' => $nombreProfesional,
                    'apellido' => $apellidoProfesional,
                    'nombre_comercial' => $nombreComercial,
                ]
            ];
        });

        return response()->json($lugaresFormateados);
    }
}