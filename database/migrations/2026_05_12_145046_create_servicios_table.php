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
    Schema::create('servicios', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->text('descripcion')->nullable();
        $table->decimal('precio', 10, 2);
        $table->integer('duracion');
        $table->string('modalidad');
        $table->integer('bufferEntreTurnos')->default(0);

        // Claves Foráneas
        $table->foreignId('profesional_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('categoria_servicio_id')->constrained('categoria_servicios');
        $table->foreignId('lugar_atencion_id')->nullable()->constrained('lugar_atencions');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
