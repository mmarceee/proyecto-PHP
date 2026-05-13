<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notificacions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reserva_id')->constrained()->onDelete('cascade');

            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo_not', ['confirmacion_reserva', 'recordatorio_turno', 
                                     'cancelacion', 'reprogramacion', 'pago_aprobado', 'mensaje_relevante'])->default('confirmacion_reserva');
            $table->enum('canal_not', ['sistema', 'email', 'push']);
            $table->enum('estado_not', ['pendiente', 'enviada', 'fallida']);
            $table->boolean('leida')->default(false);

            $table->date('fechaCreacion')->default(now());
            $table->date('fechaEnvio')->nullable();
            $table->date('fechaProgramada')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacions');
    }
};
