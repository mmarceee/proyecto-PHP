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
            
            // Relación con la compra del cliente
            $table->foreignId('compra_paquete_id')
                  ->constrained('compra_paquetes')
                  ->cascadeOnDelete();
            
            // Relación estricta 1 a 1 (0..1) con la reserva. 
            // El unique() es vital para el control de concurrencia y evitar doble cobro/consumo.
            $table->foreignId('reserva_id')
                  ->unique()
                  ->constrained('reservas')
                  ->cascadeOnDelete();
            
            // Atributo de negocio
            $table->dateTime('fechaUso')->comment('Momento exacto en el que se consumió la sesión');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uso_sesion_paquetes');
    }
};