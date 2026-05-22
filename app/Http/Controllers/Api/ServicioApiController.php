<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ServicioService;
use App\Models\CategoriaServicio; //Importamos tu modelo de categorías

class ServicioApiController extends Controller
{
    protected $servicioService;

    public function __construct(ServicioService $servicioService)
    {
        $this->servicioService = $servicioService;
    }

    private function verificarProfesional(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->profesional || $user->profesional->estado !== 'aprobado') {
            abort(response()->json(['error' => 'No autorizado.'], 403));
        }
        return $user->profesional;
    }

    public function index(Request $request)
    {
        $profesional = $this->verificarProfesional($request);
        $servicios = $this->servicioService->listarPorProfesional($profesional->id);
        
        //Traemos las categorías reales para enviárselas a JavaScript
        $categorias = CategoriaServicio::orderBy('nombre', 'asc')->get();

        return response()->json([
            'servicios' => $servicios,
            'categorias' => $categorias
        ]);
    }

    public function store(Request $request)
    {
        $profesional = $this->verificarProfesional($request);

        $validated = $request->validate([
            'nombre'                => ['required', 'string', 'max:255'],
            'descripcion'           => ['nullable', 'string'],
            'precio'                => ['required', 'numeric', 'min:0'],
            'duracion'              => ['required', 'integer', 'min:1'],
            'modalidad'             => ['required', 'in:Virtual,Presencial'],
            'bufferEntreTurnos'     => ['nullable', 'integer', 'min:0'],
            'categoria_servicio_id' => ['required', 'exists:categoria_servicios,id'], //Validación real
        ]);

        $servicio = $this->servicioService->crear($profesional, $validated);

        return response()->json(['message' => 'Servicio creado con éxito.', 'servicio' => $servicio], 201);
    }

    public function update(Request $request, $id)
    {
        $profesional = $this->verificarProfesional($request);

        $validated = $request->validate([
            'nombre'                => ['required', 'string', 'max:255'],
            'descripcion'           => ['nullable', 'string'],
            'precio'                => ['required', 'numeric', 'min:0'],
            'duracion'              => ['required', 'integer', 'min:1'],
            'modalidad'             => ['required', 'in:Virtual,Presencial'],
            'bufferEntreTurnos'     => ['nullable', 'integer', 'min:0'],
            'categoria_servicio_id' => ['required', 'exists:categoria_servicios,id'], // Validación real
        ]);

        $servicio = $this->servicioService->actualizar($id, $profesional->id, $validated);

        return response()->json(['message' => 'Servicio actualizado con éxito.', 'servicio' => $servicio]);
    }

    public function destroy(Request $request, $id)
    {
        $profesional = $this->verificarProfesional($request);
        $this->servicioService->eliminar($id, $profesional->id);

        return response()->json(['message' => 'Servicio eliminado correctamente.']);
    }
}