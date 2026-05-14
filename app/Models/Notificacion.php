<?php

namespace App\Models;

use App\Models\User;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'reserva_id', 'titulo', 'mensaje', 'tipo_not', 'canal_not', 'estado_not', 'leida', 'fechaCreacion', 'fechaEnvio', 'fechaProgramada'])]
class Notificacion extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fechaCreacion' => 'date',
            'fechaEnvio' => 'date',
            'fechaProgramada' => 'date',
        ];
    }
}
