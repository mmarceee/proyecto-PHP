<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id'])]
class Cliente extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'clientes';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
    public function compraPaquetes()
    {
        return $this->hasMany(CompraPaquete::class);
    }
    public function pago()
    {
        return $this->hasMany(Pago::class);
    }
}