<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uso_sesion_paquetes', function (Blueprint $table) {
            $table->id();
            
            // Relación con el paquete que compró el cliente (1 ---- 0..*)
            $table->foreignId('compra_paquete_id')
                  ->constrained('compra_paquetes')
                  ->cascadeOnDelete();
            
            // Relación con la reserva específica (1 ---- 0..1)
            // El índice unique() GARANTIZA a nivel SQL que una reserva no pueda consumir más de una sesión.
            $table->foreignId('reserva_id')
                  ->unique()
                  ->constrained('reservas')
                  ->cascadeOnDelete();
            
            // Atributo definido en el diagrama
            $table->dateTime('fechaUso')->comment('Momento exacto en el que se consumió o registró la sesión');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uso_sesion_paquetes');
    }
};