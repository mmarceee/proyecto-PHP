<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activa',
    ];

    /**
     * Una categoría puede tener muchos servicios asociados.
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }
}