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
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id();

            // Relación con el usuario base
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Atributos del profesional
            $table->text('descripcion');
            $table->string('especialidad');
            $table->decimal('reputacion_promedio', 3, 2)->default(0);
            $table->string('nombre_comercial')->nullable();

            // Estado de aprobación del profesional
            $table->string('estado')->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesionales');
    }
};