<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServicioService;
use App\Models\Categoria;
use App\Models\Servicio; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        
        $categorias = Categoria::query()
            ->where('activa', true)
            ->orWhereIn('id', $servicios->pluck('categoria_id'))
            ->orderBy('nombre')
            ->get();
        $lugarAtencion = $profesional->lugarAtencion;
        return response()->json([
            'servicios' => $servicios,
            'categorias' => $categorias,
            'lugar_atencion' => $lugarAtencion
        ]);
    }

    public function store(Request $request)
    {
        $profesional = $this->verificarProfesional($request);

        $validated = $request->validate([
            'nombre'                => ['required', 'string', 'max:255'],
            'descripcion'           => ['nullable', 'string'],
            'precio'                => ['required', 'numeric', 'min:0'],
            'modalidad'             => ['required', 'in:Virtual,Presencial'],
            'categoria_id'          => ['required', Rule::exists('categorias', 'id') ->where('activa', true),],
            
            // Campos requeridos solo si la modalidad es Presencial
            'lugar_nombre'          => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'lugar_direccion'       => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'lugar_ciudad'          => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'lugar_departamento'    => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'latitud'               => ['nullable', 'required_if:modalidad,Presencial', 'numeric'],
            'longitud'              => ['nullable', 'required_if:modalidad,Presencial', 'numeric'],
        ]);

        // Ahora $validated sí incluye las coordenadas, se las pasamos al servicio
        $servicio = $this->servicioService->crear($profesional, $validated);

        return response()->json(['message' => 'Servicio creado con éxito.', 'servicio' => $servicio], 201);
    }

    public function update(Request $request, $id)
    {
        $profesional = $this->verificarProfesional($request);
        
        $servicio = Servicio::where('id', $id)
            ->where('profesional_id', $profesional->id)
            ->firstOrFail();

        $validated = $request->validate([
            'nombre'                => ['required', 'string', 'max:255'],
            'descripcion'           => ['nullable', 'string'],
            'precio'                => ['required', 'numeric', 'min:0'],
            'modalidad'             => ['required', 'in:Virtual,Presencial'],
            'categoria_id'          => ['required', Rule::exists('categorias', 'id')->where(fn ($query) => $query->where('activa', true)->orWhere('id', $servicio->categoria_id))],
            
            // NUEVOS CAMPOS PARA EDICIÓN
            'lugar_nombre'          => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'lugar_direccion'       => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'lugar_ciudad'          => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'lugar_departamento'    => ['nullable', 'required_if:modalidad,Presencial', 'string', 'max:255'],
            'latitud'               => ['nullable', 'required_if:modalidad,Presencial', 'numeric'],
            'longitud'              => ['nullable', 'required_if:modalidad,Presencial', 'numeric'],
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