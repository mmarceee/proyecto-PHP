<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            
            // Relación con la reserva
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            
            // El diagrama indica que la relación es directamente con "Usuario" (realiza/recibe).
            // Al apuntar a 'users', cubrimos tanto a Clientes como a Profesionales en una sola estructura.
            $table->foreignId('evaluador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluado_id')->constrained('users')->cascadeOnDelete();
            
            // Tipos de calificación.
            $table->enum('tipoCalificacion', ['ClienteAProfesional', 'ProfesionalACliente']);
            
            // Atributos de valoración.
            $table->unsignedTinyInteger('puntuacion');
            $table->text('comentario')->nullable();
            $table->timestamp('fecha')->useCurrent(); // Fecha explícita según el diagrama.

            $table->timestamps();

            // RESTRICCIÓN ESTRICTA DE NEGOCIO: 
            // Evita duplicados. Solo puede existir UN registro 'ClienteAProfesional' por reserva, 
            // y UN registro 'ProfesionalACliente' por reserva.
            $table->unique(['reserva_id', 'tipoCalificacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};