<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['nombre', 'descripcion'])]
class CategoriaServicio extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaServicioFactory> */
    use HasFactory;

    /**
     * Una categoría puede tener muchos servicios asociados.
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }
}