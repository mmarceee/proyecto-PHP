<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicas_cancelacion', function (Blueprint $table) {
            $table->id();
            
            // Relación 1 a 1 estricta: El unique() asegura el 0..1 del diagrama
            $table->foreignId('profesional_id')
                  ->unique()
                  ->constrained('profesionales')
                  ->cascadeOnDelete();
            
            // Atributos del negocio
            // Se asume que el tiempo mínimo se mide en horas (ej. 24 horas antes)
            $table->unsignedInteger('tiempo_minimo_cancelacion')->comment('Horas de anticipación requeridas');
            $table->boolean('permite_reprogramacion')->default(false);
            $table->text('descripcion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politicas_cancelacion');
    }
};