<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaqueteServicio extends Model
{
    use HasFactory;

    // Forzamos el nombre en español pluralizado correctamente
    protected $table = 'paquetes_servicios';

    protected $fillable = [
        'profesional_id',
        'servicio_id',
        'nombre',
        'descripcion',
        'cantidad_sesiones',
        'precio',
        'validez_meses',
        'activo',
    ];

    /**
     * Casteo estricto de PHP 8+ para Laravel 11
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'cantidad_sesiones' => 'integer',
            'validez_meses' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(CompraPaquete::class, 'paquete_servicio_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Dominio
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica si el paquete está disponible para la venta en el catálogo.
     */
    public function estaActivoParaVenta(): bool
    {
        return $this->activo === true;
    }
}