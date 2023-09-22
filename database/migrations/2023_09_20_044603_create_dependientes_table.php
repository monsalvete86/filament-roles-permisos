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
            //$table->foreignId('cliente_id');
            $table->string('nombre')->nullable();
            $table->boolean('aplica_cobertura')->default(true);
            $table->foreignId('estado_migratorio_id')->constrained('estado_migratorios')->cascadeOnDelete();
            $table->date('fec_nac');
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
