<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name','apellido', 'email', 'password', 'telefono', 'estado_usuario', 'google_id', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_BLOQUEADO = 'bloqueado';
    public const ESTADO_ELIMINADO = 'eliminado';

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
        
    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    public function profesional(): HasOne
    {
        return $this->hasOne(Profesional::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de estado del usuario
    |--------------------------------------------------------------------------
    */

    public function estaActivo(): bool
    {
        return $this->estado_usuario === self::ESTADO_ACTIVO;
    }

    public function estaBloqueado(): bool
    {
        return $this->estado_usuario === self::ESTADO_BLOQUEADO;
    }

    public function estaEliminado(): bool
    {
        return $this->estado_usuario === self::ESTADO_ELIMINADO;
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos para saber qué tipo de usuario es
    |--------------------------------------------------------------------------
    */

    public function esCliente(): bool
    {
        return $this->cliente()->exists();
    }

    public function esProfesionalAprobado(): bool
    {
        return $this->profesional()
            ->where('estado', Profesional::ESTADO_APROBADO)
            ->exists();
    }

    public function esAdmin(): bool
    {
        return $this->admin()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Permisos generales
    |--------------------------------------------------------------------------
    */

    public function puedeContratar(): bool
    {
        return $this->esCliente();
    }

    public function puedeAccederPanelProfesional(): bool
    {
        return $this->esProfesionalAprobado() || $this->esAdmin();
    }

    public function puedeAccederPanelAdmin(): bool
    {
        return $this->esAdmin();
    }

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
