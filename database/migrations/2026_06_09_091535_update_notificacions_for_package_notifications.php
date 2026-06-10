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
    Schema::table('notificacions', function (Blueprint $table) {
        $table->dropForeign(['reserva_id']);
    });

    Schema::table('notificacions', function (Blueprint $table) {
        $table->foreignId('reserva_id')
            ->nullable()
            ->change();

        $table->foreignId('compra_paquete_id')
            ->nullable()
            ->after('reserva_id')
            ->constrained('compra_paquetes')
            ->nullOnDelete();

        $table->foreign('reserva_id')
            ->references('id')
            ->on('reservas')
            ->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('notificacions', function (Blueprint $table) {
        $table->dropForeign(['compra_paquete_id']);
        $table->dropColumn('compra_paquete_id');

        $table->dropForeign(['reserva_id']);
    });

    Schema::table('notificacions', function (Blueprint $table) {
        $table->foreignId('reserva_id')
            ->nullable(false)
            ->change();

        $table->foreign('reserva_id')
            ->references('id')
            ->on('reservas')
            ->cascadeOnDelete();
    });
}
};
