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
        Schema::create('videollamadas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reserva_id')->constrained()->onDelete('cascade');
            $table->string('room_name')->unique();
            $table->enum('estado', ['programada', 'activa', 'finalizada', 'expirada'])->default('programada');
            /*
                - programada: La reserva exite pero aun falta mucho tiempo.
                - activa: faltan 5min o la videollamada está en curso.
                - finalizada: La videollamada ha terminado.
                - expirada: nadie entró y pasó el tiempo.
            */

            $table->timestamp('iniciada_at')->nullable();
            $table->timestamp('finalizada_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videollamadas');
    }
};
