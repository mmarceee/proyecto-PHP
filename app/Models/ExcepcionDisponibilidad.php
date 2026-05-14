<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['profesional_id', 'fecha', 'horaInicio', 'horaFin', 'tipo', 'motivo'])]
class ExcepcionDisponibilidad extends Model
{
    /** @use HasFactory<\Database\Factories\ExcepcionDisponibilidadFactory> */
    use HasFactory;

    protected $table = 'excepciones_disponibilidad';

    /**
     * Casteo de atributos nativos de Laravel 11.
     * Garantiza que la fecha se trate siempre como un objeto Carbon.
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    /**
     * Una excepción de disponibilidad pertenece a un único profesional.
     */
    public function profesional()
    {
        return $this->belongsTo(User::class, 'profesional_id'); // Recordando la herencia: Profesional es un User
    }
}