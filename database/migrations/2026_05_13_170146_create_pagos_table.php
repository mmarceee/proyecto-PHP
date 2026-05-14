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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('reserva_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('compra_paquete_id')->nullable()->constrained('compra_paquetes')->onDelete('cascade');

            $table->decimal('monto', 8, 2);
            $table->enum('estado_pago', ['pendiente', 'aprobado', 'rechazado', 'reembolso']);
            $table->enum('metodo_pago', ['simulado', 'paypal']);
            $table->string('referencia_externa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
