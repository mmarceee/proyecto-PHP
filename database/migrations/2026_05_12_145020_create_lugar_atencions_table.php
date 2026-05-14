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
    Schema::create('lugar_atencions', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('direccion');
        $table->string('ciudad');
        $table->string('departamento');
        $table->string('pais')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lugar_atencions');
    }
};
