<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'descripcion',
    'especialidad',
    'reputacion_promedio',
    'nombre_comercial',
    'estado',
])]
class Profesional extends Model
{
    protected $table = 'profesionales';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_APROBADO = 'aprobado';
    public const ESTADO_RECHAZADO = 'rechazado';

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    public function reglasDisponibilidad(): HasMany
    {
        return $this->hasMany(ReglaDisponibilidad::class);
    }

    public function excepcionesDisponibilidad(): HasMany
    {
        return $this->hasMany(ExcepcionDisponibilidad::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function politicasCancelacion(): HasMany
    {
        return $this->hasMany(PoliticaCancelacion::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de estado del profesional
    |--------------------------------------------------------------------------
    */

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function estaAprobado(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    public function estaRechazado(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADO;
    }
}