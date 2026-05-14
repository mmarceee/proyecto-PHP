<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PoliticaCancelacion extends Model
{
    use HasFactory;

    protected $table = 'politicas_cancelacion';

    protected $fillable = [
        'profesional_id',
        'tiempo_minimo_cancelacion',
        'permite_reprogramacion',
        'descripcion',
    ];

    /**
     * Casteo estricto de atributos (Laravel 11)
     */
    protected function casts(): array
    {
        return [
            'tiempo_minimo_cancelacion' => 'integer',
            'permite_reprogramacion' => 'boolean',
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
}