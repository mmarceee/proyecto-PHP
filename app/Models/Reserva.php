<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['cliente_id', 'profesional_id', 'servicio_id', 'fecha', 'hora_inicio', 'hora_fin', 'estado_reserva', 'motivo_cancelacion'])]
class Reserva extends Model {

    
    use HasFactory;

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }
    public function profesional(){
        return $this->belongsTo(Profesional::class);
    }
    public function servicio(){
        return $this->belongsTo(Servicio::class);
    }
    public function calificaciones(){
        return $this->hasMany(Calificacion::class);
    }
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hora_inicio' => 'time',
            'hora_fin' => 'time',
        ];
    }
}