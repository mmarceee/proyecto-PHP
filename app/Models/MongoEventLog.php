<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MongoEventLog extends Model
{
    // Forzamos al modelo a usar la conexión NoSQL de MongoDB configurada en database.php
    protected $connection = 'mongodb';
    
    // Laravel Eloquent utiliza $table para determinar el nombre de la colección en MongoDB
    protected $table = 'event_logs';
    protected $collection = 'event_logs'; // Mantenemos ambas para compatibilidad

    protected $fillable = [
        'event_type',    // usuario_registrado, profesional_aprobado, etc.
        'payload',       // Datos del evento en formato JSON/Array
        'user_id',       // ID del usuario autenticado que realiza la acción
        'ip_address',    // IP origen para auditoría
        'user_agent',    // Navegador/Dispositivo para auditoría
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}