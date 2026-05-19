<?php
namespace App\Services;

use App\Models\Profesional;

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
        return $profesional;
    }

    /**
     * Rechazar y eliminar la solicitud.
     */
    public function rechazar($id)
    {
        $profesional = Profesional::findOrFail($id);
        $profesional->update(['estado' => 'rechazado']);
        return $profesional;
        
    }
}