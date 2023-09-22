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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('telefono')->unique();
            $table->string('email')->unique();
            $table->string('nombre1');
            $table->string('nombre2')->nullable();
            $table->string('apellido1');
            $table->string('apellido2')->nullable();
            $table->boolean('aplica_cobertura')->default(true);
            $table->date('fec_nac');
            $table->string('direccion');
            $table->string('codigopostal');
            $table->foreignId('estado_id')->constrained('estados')->cascadeOnDelete();
            $table->foreignId('condado_id')->constrained('condados')->cascadeOnDelete();
            $table->foreignId('ciudad_id')->constrained('ciudads')->cascadeOnDelete();
            $table->foreignId('estado_migratorio_id')->constrained('estado_migratorios')->cascadeOnDelete();
            $table->string('tipo_trabajo');
            $table->string('personas_aseguradas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
