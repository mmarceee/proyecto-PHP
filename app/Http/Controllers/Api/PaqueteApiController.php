<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaqueteService;
use App\Models\PaqueteServicio;

class PaqueteApiController extends Controller
{
    protected $paqueteService;

    // Inyectamos el Service que creamos recién
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

        $compras = \App\Models\CompraPaquete::with([
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
            ->get();

        return response()->json($paquetes);
    }
}