<?php

namespace App\Services;

use App\Models\PaqueteServicio;
use App\Models\CompraPaquete;
use App\Models\Cliente;
use App\Models\Profesional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaqueteService
{

    public function __construct(
        private NotificacionService $notificacionService
    ) {}

    /**
     * 1. Profesional: Crea un nuevo paquete en su catálogo
     */
    public function crearPaqueteCatalogo(Profesional $profesional, array $datos)
    {
        return $profesional->paqueteServicio()->create([
            'servicio_id'       => $datos['servicio_id'],
            'nombre'            => $datos['nombre'],
            'descripcion'       => $datos['descripcion'] ?? null,
            'cantidad_sesiones' => $datos['cantidad_sesiones'],
            'precio'            => $datos['precio'],
            'validez_meses'     => $datos['validez_meses'] ?? 3, // 3 meses por defecto si no lo envían
            'activo'            => $datos['activo'] ?? true,
        ]);
    }

    /**
     * 2. Cliente: Realiza la compra de un paquete
     */
    public function comprarPaquete(Cliente $cliente, PaqueteServicio $paquete)
    {
        if (!$paquete->estaActivoParaVenta()) {
            throw new \Exception("Este paquete ya no está disponible para la compra.");
        }

        // La compra se crea dentro de una transacción para dejar listo el flujo cuando se conecte la pasarela de pagos
        $compra = DB::transaction(function () use ($cliente, $paquete) {
            return CompraPaquete::create([
                'paquete_servicio_id'  => $paquete->id,
                'cliente_id'           => $cliente->id,
                'sesiones_disponibles' => $paquete->cantidad_sesiones,
                'sesiones_consumidas'  => 0,
                'estado_paquete'       => 'activo',
                'fecha_compra'         => Carbon::now(),
            ]);
        });

        $this->notificacionService->notificarCompraPaquete($compra);

        return $compra;
    }

    /**
     * 3. Sistema: Descuenta una sesión cuando el cliente reserva o asiste a un turno
     */
    public function consumirSesion(CompraPaquete $compra, $reservaId)
    {
        if (!$compra->es_valido) {
            throw new \Exception("Este paquete no tiene sesiones disponibles o está inactivo.");
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($compra, $reservaId) {
            // Descontamos 1 disponible y sumamos 1 consumida
            $compra->decrement('sesiones_disponibles');
            $compra->increment('sesiones_consumidas');

            // Si se quedó sin sesiones, lo marcamos como completado
            if ($compra->sesiones_disponibles === 0) {
                $compra->update(['estado_paquete' => 'consumido']);
            }

            $compra->uso_sesion_paquete()->create([
                'reserva_id' => $reservaId,
                'fechaUso'  => \Carbon\Carbon::now(),
            ]);
        });
        return $compra;
    }
}