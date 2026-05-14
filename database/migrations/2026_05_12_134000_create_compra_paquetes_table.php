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
        Schema::create('compra_paquetes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            //$table->foreignId('paqueteSerivcio_id')->constrained()->onDelete('cascade');
            //$table->foreignId('usoSesionPaquete_id')->constrained()->onDelete('cascade');

            $table->integer('sesiones_disponibles');
            $table->integer('sesiones_consumidas');
            $table->enum('estado_paquete', ['activo', 'consumido', 'vencido' ,'cancelado']);

            $table->date('fecha_compra');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_paquetes');
    }
};
