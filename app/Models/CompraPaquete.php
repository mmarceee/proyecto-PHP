<?php

namespace App\Models;

use App\Models\Reserva;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;


#[Fillable(['paquete_serivcio_id','cliente_id','sesiones_disponibles', 'sesiones_consumidas', 'estado_paquete', 'fecha_compra'])]
class CompraPaquete extends Model
{
    use HasFactory;

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }
    public function reserva()
    {
        return $this->hasMany(Reserva::class);
    }
    public function uso_sesion_paquete()
    {
        return $this->hasMany(UsoSesionPaquete::class);
    }


    protected function esValido(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sesiones_disponibles > 0 && $this->estado_paquete === 'activo',
        );
    }

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'date',
        ];
    }
}
