<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['profesional_id','nombre', 'direccion', 'ciudad', 'departamento', 'pais', 'latitud', 'longitud', 'longitud'])]
class LugarAtencion extends Model
{
    /** @use HasFactory<\Database\Factories\LugarAtencionFactory> */
    use HasFactory;

    /**
     * Un lugar de atención pertenece a un profesional.
     */
    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }

    /**
     * En un lugar de atención se pueden ofrecer muchos servicios.
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }
}