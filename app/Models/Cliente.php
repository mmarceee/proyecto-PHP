<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id'])]
class Cliente extends Model
{
    protected $table = 'clientes';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}