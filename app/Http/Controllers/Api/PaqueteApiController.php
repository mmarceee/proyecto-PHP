<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaqueteService;
use App\Models\PaqueteServicio;
use App\Models\CompraPaquete;

class PaqueteApiController extends Controller
{
    protected $paqueteService;

    public function __construct(PaqueteService $paqueteService)
    {
        $this->paqueteService = $paqueteService;
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS PARA EL PROFESIONAL
    |--------------------------------------------------------------------------
    */

    /**
     * Lista todos los paquetes del catálogo del profesional logueado
     */
    public function index(Request $request)
    {
        $profesional = $request->user()->profesional;
        if (!$profesional) return response()->json(['error' => 'No autorizado'], 403);

        // Traemos los paquetes junto con el nombre del servicio al que pertenecen
        $paquetes = $profesional->paqueteServicio()->with('servicio:id,nombre')->get();

        return response()->json($paquetes);
    }

    /**
     * Lista los paquetes vendidos por el profesional logueado
     */
    public function vendidos(Request $request)
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $compras = CompraPaquete::with([
                'cliente.user',
                'paqueteServicio.servicio',
            ])
            ->whereHas('paqueteServicio', function ($query) use ($profesional) {
                $query->where('profesional_id', $profesional->id);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($compras);
    }

    /**
     * Crea un nuevo paquete en el catálogo
     */
    public function store(Request $request)
    {
        $profesional = $request->user()->profesional;
        if (!$profesional) return response()->json(['error' => 'No autorizado'], 403);

        // Validamos los datos que llegan de Alpine.js
        $datos = $request->validate([
            'servicio_id'       => 'required|exists:servicios,id',
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'cantidad_sesiones' => 'required|integer|min:2',
            'precio'            => 'required|numeric|min:0',
            'validez_meses'     => 'nullable|integer|min:1',
            'activo'            => 'boolean',
        ]);

        try {
            // Delegamos la lógica pesada al Service
            $paquete = $this->paqueteService->crearPaqueteCatalogo($profesional, $datos);
            
            return response()->json([
                'message' => 'Paquete creado exitosamente', 
                'paquete' => $paquete
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el paquete: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Pausa o Activa un paquete (para no borrarlo y perder el historial)
     */
    public function toggleActivo(Request $request, $id)
    {
        $profesional = $request->user()->profesional;
        
        // Buscamos el paquete asegurándonos de que le pertenezca a este profesional
        $paquete = PaqueteServicio::where('profesional_id', $profesional->id)->findOrFail($id);

        $paquete->update(['activo' => !$paquete->activo]);

        return response()->json([
            'message' => $paquete->activo ? 'Paquete activado' : 'Paquete pausado',
            'activo'  => $paquete->activo
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS PARA EL CLIENTE
    |--------------------------------------------------------------------------
    */

    /**
     * Lista los paquetes que el cliente ha adquirido
     */
    public function misPaquetes(Request $request)
    {
        $cliente = $request->user()->cliente;
        if (!$cliente) return response()->json(['error' => 'No autorizado'], 403);

        $compras = CompraPaquete::with([
                'paqueteServicio.servicio', 
                'paqueteServicio.profesional.user'
            ])
            ->where('cliente_id', $cliente->id)
            ->orderBy('estado_paquete', 'asc') // Los 'activos' primero
            ->orderBy('created_at', 'desc')    // Los más recientes primero
            ->get();

        return response()->json($compras);
    }

    /**
     * Procesa la compra de un paquete por parte del cliente
     */
    public function comprar(Request $request, $idPaquete)
    {
        $cliente = $request->user()->cliente;
        if (!$cliente) return response()->json(['error' => 'Solo los clientes pueden comprar paquetes'], 403);

        $paquete = PaqueteServicio::findOrFail($idPaquete);

        try {
            $compra = $this->paqueteService->comprarPaquete($cliente, $paquete);
            
            return response()->json([
                'message' => '¡Paquete adquirido con éxito!', 
                'compra'  => $compra
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Lista TODOS los paquetes activos en la plataforma para la tienda
     */
    public function disponibles()
    {
        // Traemos paquetes activos, incluyendo datos del servicio y del profesional (usuario)
        $paquetes = PaqueteServicio::with(['servicio', 'profesional.user'])
            ->where('activo', true)
            ->whereHas('profesional', function ($profesionalQuery) {
                $profesionalQuery
                    ->where('estado', 'aprobado')
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->where('estado_usuario', 'activo');
                    });
            })
            ->get();

        return response()->json($paquetes);
    }

    /**
     * Verifica si el cliente tiene un paquete con saldo para un servicio específico
     */
    public function verificarDisponibilidad(Request $request)
    {
        $cliente = $request->user()->cliente;
        $servicioId = $request->query('servicio_id');

        if (!$cliente || !$servicioId) {
            return response()->json(['tiene_paquete' => false]);
        }

        // Buscamos una compra ACTIVA, que tenga saldo, y que coincida con el servicio
        $compraActiva = CompraPaquete::where('cliente_id', $cliente->id)
            ->where('estado_paquete', 'activo')
            ->where('sesiones_disponibles', '>', 0)
            ->whereHas('paqueteServicio', function($q) use ($servicioId) {
                $q->where('servicio_id', $servicioId);
            })
            ->with('paqueteServicio')
            ->first();

        if ($compraActiva) {
            return response()->json([
                'tiene_paquete' => true,
                'paquete' => [
                    'id' => $compraActiva->id,
                    'nombre' => $compraActiva->paqueteServicio->nombre,
                    'disponibles' => $compraActiva->sesiones_disponibles
                ]
            ]);
        }

        return response()->json(['tiene_paquete' => false]);
    }

    /**
     * Devuelve el historial detallado de sesiones consumidas de un paquete
     */
    public function historialConsumo(Request $request, $id)
    {
        $cliente = $request->user()->cliente;

        // Buscamos la compra asegurándonos de que sea de este cliente
        $compra = CompraPaquete::where('id', $id)
            ->where('cliente_id', $cliente->id)
            ->with(['uso_sesion_paquete.reserva.profesional.user']) 
            ->firstOrFail();

        // Formateamos los datos para que el JS los dibuje fácil
        $historial = $compra->uso_sesion_paquete->map(function ($uso) {
            return [
                'id' => $uso->id,
                'fecha_consumo' => $uso->fechaUso ? $uso->fechaUso->format('d/m/Y') : 'N/A',
                'reserva_fecha' => $uso->reserva ? \Carbon\Carbon::parse($uso->reserva->fecha)->format('d/m/Y') : 'N/A',
                'reserva_hora' => $uso->reserva ? \Carbon\Carbon::parse($uso->reserva->hora_inicio)->format('H:i') : 'N/A',
                'profesional' => $uso->reserva ? ($uso->reserva->profesional->user->name . ' ' . ($uso->reserva->profesional->user->last_name ?? '')) : 'N/A',
                'estado_reserva' => $uso->reserva->estado_reserva ?? 'N/A'
            ];
        })->sortByDesc('id')->values(); // Los usos más recientes arriba

        return response()->json($historial);
    }
}