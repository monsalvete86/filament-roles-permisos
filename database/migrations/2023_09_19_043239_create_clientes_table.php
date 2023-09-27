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
            //$table->foreignId('dependiente_id')->nullable();
            $table->string('telefono')->unique();
            $table->string('email')->unique();
            $table->string('nombre1')->nullable();
            $table->string('nombre2')->nullable();
            $table->string('apellido1')->nullable();
            $table->string('apellido2')->nullable();
            $table->boolean('aplica_cobertura')->default(true);
            $table->date('fec_nac')->nullable();
            $table->string('direccion')->nullable();
            $table->string('codigopostal');
            $table->foreignId('estado_id')->constrained('estados')->cascadeOnDelete();
            $table->foreignId('condado_id')->constrained('condados')->cascadeOnDelete();
            $table->foreignId('ciudad_id')->constrained('ciudads')->cascadeOnDelete();
            $table->foreignId('estado_migratorio_id')->constrained('estado_migratorios')->cascadeOnDelete();
            $table->string('tipo_trabajo');
            $table->string('personas_aseguradas');
            $table->string('estado_civil_conyugue')->nullable();
            $table->string('nombre_conyugue')->nullable();
            $table->boolean('aplica_covertura_conyugue')->default(true);
            $table->boolean('dependientes_fuera_pareja')->default(true);
            $table->string('quien_aporta_ingresos')->nullable();
            $table->string('quien_declara_taxes')->nullable();
            $table->float('total_ingresos_gf')->nullable();
            $table->string('estado_cliente')->nullable();
            $table->foreignId('digitador_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('fecha_digitadora')->nullable();
            $table->foreignId('benefit_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('fecha_benefit')->nullable();
            $table->foreignId('procesador_id')->constrained('users')->cascadeOnDelete();
            $table->integer('codigo_anterior')->nullable();
            $table->string('cobertura_ant')->nullable();
            $table->string('ultimo_agente')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->date('inicio_cobertura')->nullable();
            $table->date('fin_cobertura')->nullable();
            $table->string('agente')->nullable();
            $table->date('inicio_cobertura_vig')->nullable();
            $table->date('fin_cobertura_vig')->nullable();
            $table->date('fecha_retiro_cobertura_ant')->nullable();
            $table->string('imagen')->nullable();
            $table->text('nota_benefit')->nullable();
            $table->text('nota_procesador')->nullable();
            $table->text('nota_digitadora')->nullable();
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
