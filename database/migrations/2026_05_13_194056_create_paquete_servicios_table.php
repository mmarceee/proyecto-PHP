<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquetes_servicios', function (Blueprint $table) {
            $table->id();
            
            // Relaciones estandarizadas a las tablas maestras
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            
            // Atributos comerciales del paquete
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            
            // Reglas de negocio duras para el consumo
            $table->unsignedInteger('cantidad_sesiones')->comment('Total de reservas que permite este paquete');
            $table->decimal('precio', 10, 2)->comment('Precio final del paquete (suele tener descuento vs unitario)');
            $table->unsignedInteger('validez_meses')->default(1)->comment('Caducidad en meses desde la compra');
            
            // Soft-delete lógico para el catálogo (el profesional puede dejar de ofrecerlo sin afectar compras previas)
            $table->boolean('activo')->default(true);

            $table->timestamps();
            
            // Índice compuesto para optimizar las búsquedas del catálogo del profesional
            $table->index(['profesional_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquetes_servicios');
    }
};