<?php

namespace App\Models;

use App\Models\Reserva;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['paqueteSerivcio_id','sesiones_disponibles', 'sesiones_consumidas', 'estado_paquete', 'fecha_compra'])]
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

    protected function casts(): array
    {
        return [
            'fechaCompra' => 'date',
        ];
    }
}
