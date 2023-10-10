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
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminamos la columna estado_migratorio_coyugue
            $table->dropColumn('estado_migratorio_coyugue');

            // Agregamos la columna estado_migratorio_id como llave foranea
            $table->foreignId('estado_migratorio_id')
                ->constrained('estado_migratorios')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminamos la llave foranea estado_migratorio_id
            $table->dropForeign('clientes_estado_migratorio_id_foreign');

            // Agregamos la columna estado_migratorio_coyugue
            $table->string('estado_migratorio_coyugue')->nullable();
        });
    }
};
