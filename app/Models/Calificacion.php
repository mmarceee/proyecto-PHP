<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Calificacion extends Model
{

    /** @use HasFactory<\Database\Factories\CategoriaServicioFactory> */
    use HasFactory;
    
    protected $table = 'calificaciones';

    protected $fillable = [
        'reserva_id',
        'evaluador_id',
        'evaluado_id',
        'tipoCalificacion',
        'puntuacion',
        'comentario',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion' => 'integer',
            'fecha' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    /**
     * El usuario que realiza la calificación.
     */
    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }

    /**
     * El usuario que recibe la calificación.
     */
    public function evaluado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluado_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Dominio
    |--------------------------------------------------------------------------
    */

    public function esDeClienteAProfesional(): bool
    {
        return $this->tipoCalificacion === 'ClienteAProfesional';
    }

    public function esDeProfesionalACliente(): bool
    {
        return $this->tipoCalificacion === 'ProfesionalACliente';
    }
}