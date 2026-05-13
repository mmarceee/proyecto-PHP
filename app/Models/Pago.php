<?php

namespace App\Models;
use App\Models\Reserva;
use App\Models\CompraPaquete;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['reserva_id', 'compra_paquete_id', 'monto', 'estado_pago', 'metodo_pago', 'referencia_externa'])]
class Pago extends Model
{
    use HasFactory;

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function compraPaquete()
    {
        return $this->belongsTo(CompraPaquete::class);
    }
}
