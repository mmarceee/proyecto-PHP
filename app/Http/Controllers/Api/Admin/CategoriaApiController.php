<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\CategoriaService;
use Illuminate\Http\Request;


class CategoriaApiController extends Controller
{
    protected CategoriaService $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    public function listarCategorias()
    {
        $user = auth()->user();

        $categorias = $this->categoriaService->listarCategorias();

        return response()->json([
            'categorias' => $categorias
        ]);
    }

    public function crearCategoria(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $categoria = $this->categoriaService->crearCategoria($datos);

        return response()->json([
            'mensaje' => 'Categoría creada correctamente.',
            'categoria' => $categoria,
        ], 201);
    }

    public function actualizarCategoria(Request $request, $id)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias,nombre,' . $id],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activa' => ['boolean'],
        ]);

        $categoria = $this->categoriaService->actualizarCategoria($id, $datos);

        return response()->json([
            'mensaje' => 'Categoría actualizada correctamente.',
            'categoria' => $categoria,
        ]);
    }

    public function desactivarCategoria($id)
    {
        $categoria = $this->categoriaService->desactivarCategoria($id);

        return response()->json([
            'mensaje' => 'Categoría desactivada correctamente.',
            'categoria' => $categoria
        ]);
    }

    public function activarCategoria($id)
    {
        $categoria = $this->categoriaService->activarCategoria($id);

        return response()->json([
            'mensaje' => 'Categoría activada correctamente.',
            'categoria' => $categoria
        ]);
    }
}