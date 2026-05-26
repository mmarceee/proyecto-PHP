<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nombre', 'descripcion', 'precio', 'duracion', 'modalidad', 'bufferEntreTurnos', 'categoria_servicio_id'])]
class Servicio extends Model
{
    /** @use HasFactory<\Database\Factories\ServicioFactory> */
    use HasFactory;

    use SoftDeletes;
    // --- RELACIONES ---

   
    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function CategoriaServicio()
    {
        return $this->belongsTo(CategoriaServicio::class);
    }

    public function PaqueteServicio()
    {
        return $this->hasMany(PaqueteServicio::class);
    }

    public function LugarAtencion()
    {
        return $this->belongsTo(LugarAtencion::class);
    }
}