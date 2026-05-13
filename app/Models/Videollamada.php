<?php

namespace App\Models;

use App\Models\Reserva;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['reserva_id', 'room_name', 'estado', 'iniciada_at', 'finalizada_at'])]
class Videollamada extends Model
{
    use HasFactory;

    public function reserva(){
        return $this->belongsTo(Reserva::class);
    }

    protected function casts(): array
    {
        return [
            'iniciada_at' => 'datetime',
            'finalizada_at' => 'datetime',
        ];
    }

}
