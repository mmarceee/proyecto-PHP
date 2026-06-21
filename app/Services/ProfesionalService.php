<?php
namespace App\Services;

use App\Models\Profesional;
use App\Services\EventLogService;

class ProfesionalService
{
    /** 
    * Obtener las solicitudes de profesionales pendientes de aprobación
    */
    public function obtenerPendientes()
    {
        return Profesional::where('estado', 'pendiente')
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($profesional) {
                return [
                    'id' => $profesional->id,
                    'name' => trim(
                            ($profesional->user?->name ?? '') . ' ' . ($profesional->user?->apellido ?? '')
                        ),
                    'email' => $profesional->user->email,
                    'telefono' => $profesional->user->telefono,
                    'especialidad' => $profesional->especialidad,
                    'nombre_comercial' => $profesional->nombre_comercial,
                    'descripcion' => $profesional->descripcion,
                    'estado' => $profesional->estado,
                    'created_at' => $profesional->created_at->diffForHumans(),
                ];
            });      
    }

    /**
     * Aprobar la solicitud de un profesional.
     */
    public function aprobar($id)
    {
        $profesional = Profesional::findOrFail($id);
        $profesional->update(['estado' => 'aprobado']);

        // REGISTRO DE AUDITORÍA NOSQL
        app(EventLogService::class)->log('profesional_aprobado', [
            'profesional_id' => $profesional->id,
            'user_id'        => $profesional->user_id,
            'name'           => trim(($profesional->user?->name ?? '') . ' ' . ($profesional->user?->apellido ?? '')),
            'especialidad'   => $profesional->especialidad,
        ], auth()->id());


        return $profesional;
    }

    /**
     * Rechazar y eliminar la solicitud.
     */
    public function rechazar($id)
    {
        $profesional = Profesional::findOrFail($id);
        $profesional->update(['estado' => 'rechazado']);

        // REGISTRO DE AUDITORÍA NOSQL
        app(\App\Services\EventLogService::class)->log('profesional_rechazado', [
            'profesional_id' => $profesional->id,
            'user_id'        => $profesional->user_id,
            'name'           => trim(($profesional->user?->name ?? '') . ' ' . ($profesional->user?->apellido ?? '')),
            'especialidad'   => $profesional->especialidad,
        ], auth()->id());

        return $profesional;
        
    }

    /**
     * Crear una nueva postulación de profesional
     */
    public function crearPostulacion($user, array $datos)
    {
        // Si ya tiene perfil profesional, lanzamos excepción
        if ($user->profesional()->exists()) {
            throw new \Exception('Ya has enviado una solicitud previamente.');
        }

        return $user->profesional()->create([
            'especialidad'        => $datos['especialidad'],
            'descripcion'         => $datos['descripcion'],
            'nombre_comercial'    => $datos['nombre_comercial'] ?? null,
            'reputacion_promedio' => 0.00,
            'estado'              => 'pendiente',
        ]);
    }
}