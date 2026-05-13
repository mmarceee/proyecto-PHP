<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsoSesionPaquete extends Model
{
    use HasFactory;

    protected $table = 'uso_sesion_paquetes';

    protected $fillable = [
        'compra_paquete_id',
        'reserva_id',
        'fechaUso',
    ];

    /**
     * Casteo estricto de atributos (Laravel 11)
     */
    protected function casts(): array
    {
        return [
            'fechaUso' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function compraPaquete(): BelongsTo
    {
        return $this->belongsTo(CompraPaquete::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }
}