<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\CompraPaquete;
use App\Models\Calificacion;
use App\Models\Videollamada;
use App\Models\Notificacion;

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
        return $this->belongsTo(Servicio::class)->withTrashed();
    }
    public function calificaciones(){
        return $this->hasMany(Calificacion::class);
    }
    public function videollamada(){
        return $this->hasOne(Videollamada::class);
    }
    public function notificaciones(){
        return $this->hasMany(Notificacion::class);
    }
    public function pago(){
        return $this->hasOne(Pago::class);
    }
    public function uso_sesion_paquete(){
        return $this->hasOne(UsoSesionPaquete::class);
    }

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }
}