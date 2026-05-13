<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excepciones_disponibilidad', function (Blueprint $table) {
            $table->id();
            
            // Relación estricta con la tabla base de usuarios (Jerarquía)
            $table->foreignId('profesional_id')->constrained('users')->onDelete('cascade');
            
            $table->date('fecha');
            
            // Nullable porque un feriado o licencia puede abarcar el día completo
            $table->time('horaInicio')->nullable();
            $table->time('horaFin')->nullable();
            
            // Tipos según las reglas de negocio (No disponible, Disponible extra, Licencia, Feriado)
            $table->enum('tipo', ['no_disponible', 'disponible_extra', 'licencia', 'feriado']);
            
            $table->string('motivo')->nullable();

            $table->timestamps();

            // KEY de Optimización: Crucial para el rendimiento del Service Pattern al calcular agendas
            $table->index(['profesional_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excepciones_disponibilidad');
    }
};