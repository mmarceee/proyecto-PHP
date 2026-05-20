<?php

namespace App\Models;

use App\Models\Profesional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['profesional_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'duracion_turno', 'buffer_tiempo'])]
class ReglaDisponibilidad extends Model
{
    use HasFactory;

    protected $table = 'regla_disponibilidads';

    public function profesional(){
        return $this->belongsTo(Profesional::class);
    }
}
