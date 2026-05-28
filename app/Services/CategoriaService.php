<?php

namespace App\Services;

use App\Models\Categoria;

class CategoriaService
{
    public function listarCategorias()
    {
        return Categoria::orderBy('nombre')->get();
    }

    public function crearCategoria(array $datos): Categoria
    {
        return Categoria::create([
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activa' => true,
        ]);
    }
    
    public function actualizarCategoria(int $id, array $datos): Categoria
    {
        $categoria = Categoria::findOrFail($id);

        $categoria->update([
        'nombre' => $datos['nombre'],
        'descripcion' => $datos['descripcion'] ?? null,
        'activa' => $datos['activa'] ?? $categoria->activa,
        ]);
        
        return $categoria;
    }

    public function desactivarCategoria(int $id): Categoria
    {
        $categoria = Categoria::findOrFail($id);

        $categoria->update([
            'activa' => false,
        ]);

        return $categoria;
    }

    public function activarCategoria(int $id): Categoria
    {
        $categoria = Categoria::findOrFail($id);

        $categoria->update([
            'activa' => true,
        ]);

        return $categoria;
    }
}

