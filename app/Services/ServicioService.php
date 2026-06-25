<?php

namespace App\Services;

use App\Models\Servicio;
use App\Models\LugarAtencion;
use Illuminate\Support\Facades\DB;

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
            
            // Guardamos el servicio
            $servicio = $profesional->servicios()->create([
                'nombre'                => $datos['nombre'],
                'descripcion'           => $datos['descripcion'] ?? null,
                'precio'                => $datos['precio'],
                'duracion'              => $datos['duracion'] ?? 60,
                'modalidad'             => $datos['modalidad'],
                'bufferEntreTurnos'     => 0,
                'categoria_id' => $datos['categoria_id'], 
            ]);

            // Regla de Negocio: Solo si es Presencial guardamos el Lugar de Atención
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

            // Actualizamos los datos básicos del servicio
            $servicio->update([
                'nombre'                => $datos['nombre'],
                'descripcion'           => $datos['descripcion'] ?? null,
                'precio'                => $datos['precio'],
                'duracion'              => $datos['duracion'] ?? 60,
                'modalidad'             => $datos['modalidad'],
                'bufferEntreTurnos'     => 0,
                'categoria_id'          => $datos['categoria_id'],
            ]);

            // Actualizamos o Creamos el lugar si la modalidad (nueva o actual) es Presencial
            if ($datos['modalidad'] === 'Presencial') {
                LugarAtencion::updateOrCreate(
                    [
                        'profesional_id' => $profesionalId,
                    ],
                    [
                        'nombre'         => $datos['lugar_nombre'],
                        'direccion'      => $datos['lugar_direccion'],
                        'ciudad'         => $datos['lugar_ciudad'],
                        'departamento'   => $datos['lugar_departamento'],
                        'pais'           => 'Uruguay',
                        'latitud'        => $datos['latitud'],
                        'longitud'       => $datos['longitud'],
                    ]
                );
            } else {
                $this->limpiarPinDeUbicacionSiCorresponde($profesionalId);
            }

            return $servicio;
        });
    }

    public function eliminar($id, $profesionalId)
    {
        // Envolvemos el query de eliminación masiva y la limpieza de cascada en una transacción
        return DB::transaction(function () use ($id, $profesionalId) {
            $eliminado = Servicio::where('id', $id)
                ->where('profesional_id', $profesionalId)
                ->delete();
            if ($eliminado) {
                // Ejecutamos la evaluación de limpieza de ubicación tras borrar el servicio
                $this->limpiarPinDeUbicacionSiCorresponde($profesionalId);
            }
            return $eliminado;
        });
    }

     /**
     * Elimina el LugarAtencion del profesional si ya no ofrece ningún servicio presencial activo.
     */
    private function limpiarPinDeUbicacionSiCorresponde(int $profesionalId): void
    {
        $tienePresenciales = Servicio::where('profesional_id', $profesionalId)
            ->where('modalidad', 'Presencial')
            ->exists();
        if (!$tienePresenciales) {
            LugarAtencion::where('profesional_id', $profesionalId)->delete();
        }
    }
}