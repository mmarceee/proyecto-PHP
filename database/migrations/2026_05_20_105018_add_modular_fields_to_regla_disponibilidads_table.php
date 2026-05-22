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
        Schema::table('regla_disponibilidads', function (Blueprint $table) {
            // Duración del turno en minutos (por defecto 60 min)
            $table->unsignedInteger('duracion_turno')->default(60)->after('hora_fin');
            // Tiempo de descanso en minutos (por defecto 0 min)
            $table->unsignedInteger('buffer_tiempo')->default(0)->after('duracion_turno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regla_disponibilidads', function (Blueprint $table) {
            $table->dropColumn(['duracion_turno', 'buffer_tiempo']);
        });
    }
};
