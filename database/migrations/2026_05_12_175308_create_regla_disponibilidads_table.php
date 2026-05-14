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
        Schema::create('regla_disponibilidads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('profesional_id')->constrained('profesionales')->onDelete('cascade');

            $table->integer('dia_semana'); // 0 (domingo) a 6 (sábado)
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->timestamps();
        });

        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regla_disponibilidads');
    }
};

