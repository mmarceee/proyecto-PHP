<?php

namespace App\Services;

use App\Models\Profesional;
use App\Models\Categoria;

class BusquedaService
{
    public function buscarProfesionales(?string $queryText, ?string $categoria)
    {
        $query = Profesional::with(['user', 'servicios'])
            ->where('estado', 'aprobado');

        if ($queryText) {
            $query->where(function ($q) use ($queryText) {
                $q->whereHas('user', function ($userQuery) use ($queryText) {
                    $userQuery->where('name', 'like', "%{$queryText}%");
                })
                ->orWhere('nombre_comercial', 'like', "%{$queryText}%")
                ->orWhereHas('servicios', function ($servicioQuery) use ($queryText) {
                    $servicioQuery->where('nombre', 'like', "%{$queryText}%")
                        ->orWhere('descripcion', 'like', "%{$queryText}%")
                        ->orWhereHas('categoria', function ($categoriaQuery) use ($queryText) {
                            $categoriaQuery->where('nombre', 'like', "%{$queryText}%");
                        });
                });
            });
        }

        if ($categoria && $categoria !== 'Todas las categorías') {
            $query->whereHas('servicios.categoria', function ($q) use ($categoria) {
                $q->where('nombre', $categoria);
            });
        }

        return $query->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => trim(($p->user?->name ?? '') . ' ' . ($p->user?->apellido ?? '')) ?: 'Profesional',
                'nombre_comercial' => $p->nombre_comercial,
                'servicios' => $p->servicios->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'precio' => $s->precio ?? 0,
                        'modalidad' => $s->modalidad ?? 'Virtual',
                    ];
                }),
            ];
        });
    }

    public function obtenerCategoriasServicios()
    {
        return Categoria::query()
            ->orderBy('nombre')
            ->pluck('nombre')
            ->values();
    }
}