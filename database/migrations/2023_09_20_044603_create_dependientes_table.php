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
        Schema::create('dependientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre1_dependiente')->nullable();
            $table->string('nombre2_dependiente')->nullable();
            $table->string('apellido1_dependiente')->nullable();
            $table->string('apellido2_dependiente')->nullable();
            $table->boolean('aplica_cobertura')->default(true);
            $table->foreignId('estado_migratorio_id')->constrained('estado_migratorios')->cascadeOnDelete();
            $table->date('fec_nac')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependientes');
    }
};
