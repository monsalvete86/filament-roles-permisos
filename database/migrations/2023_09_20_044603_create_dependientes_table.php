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
            $table->string('dependiente1')->nullable();
            $table->boolean('aplica_cobertura1')->default(true);
            $table->integer('estado_migratorio1');
            $table->date('fec_nac1');
            $table->string('dependiente2')->nullable();
            $table->boolean('aplica_cobertura2')->default(true);
            $table->integer('estado_migratorio2');
            $table->date('fec_nac2');
            $table->string('dependiente3')->nullable();
            $table->boolean('aplica_cobertura3')->default(true);
            $table->integer('estado_migratorio3');
            $table->date('fec_nac3');
            $table->string('dependiente4')->nullable();
            $table->boolean('aplica_cobertura4')->default(true);
            $table->integer('estado_migratorio4');
            $table->date('fec_nac4');
            $table->string('dependiente_x')->nullable();
            $table->boolean('aplica_cobertura_x')->default(true);
            $table->integer('estado_migratorio_x');
            $table->date('fec_nac_x');
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
