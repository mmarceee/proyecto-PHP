<?php

namespace App\Services;

use App\Models\Servicio;

class ServicioService
{
    public function listarPorProfesional($profesionalId)
    {
        return Servicio::where('profesional_id', $profesionalId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function crear($profesional, array $datos)
    {
        //Se guarda de forma dinámica mediante la relación
        return $profesional->servicios()->create([
            'nombre'                => $datos['nombre'],
            'descripcion'           => $datos['descripcion'] ?? null,
            'precio'                => $datos['precio'],
            'duracion'              => $datos['duracion'],
            'modalidad'             => $datos['modalidad'],
            'bufferEntreTurnos'     => $datos['bufferEntreTurnos'] ?? 0,
            'categoria_servicio_id' => $datos['categoria_servicio_id'], 
        ]);
    }

    public function actualizar($id, $profesionalId, array $datos)
    {
        $servicio = Servicio::where('id', $id)
            ->where('profesional_id', $profesionalId)
            ->firstOrFail();

        $servicio->update([
            'nombre'                => $datos['nombre'],
            'descripcion'           => $datos['descripcion'] ?? null,
            'precio'                => $datos['precio'],
            'duracion'              => $datos['duracion'],
            'modalidad'             => $datos['modalidad'],
            'bufferEntreTurnos'     => $datos['bufferEntreTurnos'] ?? 0,
            'categoria_servicio_id' => $datos['categoria_servicio_id'],
        ]);

        return $servicio;
    }

    public function eliminar($id, $profesionalId)
    {
        return Servicio::where('id', $id)
            ->where('profesional_id', $profesionalId)
            ->delete();
    }
}