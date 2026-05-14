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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();

            //claves foraneas
            $table->foreignId('cliente_id')->constrained;
            $table->foreignId('profesional_id')->constrained;
            $table->foreignId('servicio_id')->constrained;
            $table->foreignId('compra_paquete_id')->nullable()->constrained()->onDelete('cascade');

            //atributos de la reserva
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado_reserva', ['pendiente', 'confirmada', 'pagada', 'en_curso', 'finalizada', 'cancelada', 'no_asistida']);
            $table->string('motivo_cancelacion')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
