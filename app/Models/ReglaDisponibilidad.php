<?php

namespace App\Models;

use App\Models\Profesional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['profesional_id', 'dia_semana', 'hora_inicio', 'hora_fin'])]
class ReglaDisponibilidad extends Model
{
    use HasFactory;

    public function profesional(){
        return $this->belongsTo(Profesional::class);
    }

    protected function casts(): array
    {
        return [
            'hora_inicio' => 'time:H:i',
            'hora_fin' => 'time:H:i'
        ];
    }
}
