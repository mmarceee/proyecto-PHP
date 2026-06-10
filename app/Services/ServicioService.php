<?php

namespace App\Services;

use App\Models\Servicio;
use App\Models\LugarAtencion; // Importamos el modelo del Lugar
use Illuminate\Support\Facades\DB; // Importamos la fachada DB para transacciones

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
        // Envolvemos todo en una transacción para garantizar integridad de datos
        return DB::transaction(function () use ($profesional, $datos) {
            
            // 1. Guardamos el servicio como ya lo venías haciendo
            $servicio = $profesional->servicios()->create([
                'nombre'                => $datos['nombre'],
                'descripcion'           => $datos['descripcion'] ?? null,
                'precio'                => $datos['precio'],
                'duracion'              => $datos['duracion'],
                'modalidad'             => $datos['modalidad'],
                'bufferEntreTurnos'     => $datos['bufferEntreTurnos'] ?? 0,
                'categoria_id' => $datos['categoria_id'], 
            ]);

            // 2. Regla de Negocio: Solo si es Presencial guardamos el Lugar de Atención
            if ($datos['modalidad'] === 'Presencial') {
                // updateOrCreate busca si el profesional ya tiene un lugar con ese nombre,
                // si existe lo actualiza con las nuevas coordenadas, si no, lo crea.
                LugarAtencion::updateOrCreate(
                    [
                        'profesional_id' => $profesional->id,
                        'nombre'         => $datos['lugar_nombre']
                    ],
                    [
                        'direccion'      => $datos['lugar_direccion'],
                        'ciudad'         => $datos['lugar_ciudad'],
                        'departamento'   => $datos['lugar_departamento'],
                        'pais'           => 'Uruguay',
                        'latitud'        => $datos['latitud'],
                        'longitud'       => $datos['longitud'],
                    ]
                );
            }

            return $servicio;
        });
    }

    public function actualizar($id, $profesionalId, array $datos)
    {
        return DB::transaction(function () use ($id, $profesionalId, $datos) {
            
            $servicio = Servicio::where('id', $id)
                ->where('profesional_id', $profesionalId)
                ->firstOrFail();

            // 1. Actualizamos los datos básicos del servicio
            $servicio->update([
                'nombre'                => $datos['nombre'],
                'descripcion'           => $datos['descripcion'] ?? null,
                'precio'                => $datos['precio'],
                'duracion'              => $datos['duracion'],
                'modalidad'             => $datos['modalidad'],
                'bufferEntreTurnos'     => $datos['bufferEntreTurnos'] ?? 0,
                'categoria_id'          => $datos['categoria_id'],
            ]);

            // 2. Actualizamos o Creamos el lugar si la modalidad (nueva o actual) es Presencial
            if ($datos['modalidad'] === 'Presencial') {
                LugarAtencion::updateOrCreate(
                    [
                        'profesional_id' => $profesionalId,
                        'nombre'         => $datos['lugar_nombre']
                    ],
                    [
                        'direccion'      => $datos['lugar_direccion'],
                        'ciudad'         => $datos['lugar_ciudad'],
                        'departamento'   => $datos['lugar_departamento'],
                        'pais'           => 'Uruguay',
                        'latitud'        => $datos['latitud'],
                        'longitud'       => $datos['longitud'],
                    ]
                );
            }

            return $servicio;
        });
    }

    public function eliminar($id, $profesionalId)
    {
        return Servicio::where('id', $id)
            ->where('profesional_id', $profesionalId)
            ->delete();
    }
}