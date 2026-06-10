<?php

namespace App\Models;
use App\Models\Reserva;
use App\Models\CompraPaquete;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'reserva_id', 
        'compra_paquete_id', 
        'monto', 
        'estado_pago', 
        'metodo_pago', 
        'referencia_externa'
    ];
    
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
