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
            $table->string('personas_aseguradas');  //de aqui comienza todo.
            $table->string('estado_civil_conyugue');
            $table->string('nombre_conyugue');
            $table->boolean('aplica_covertura_conyugue')->default(true);
            $table->boolean('dependientes_fuera_pareja')->default(true);
            $table->string('quien_aporta_ingresos');
            $table->string('quien_declara_taxes');
            $table->float('total_ingresos_gf');
            $table->string('estado_cliente');
            // $table->foreignId('digitadora_id')->constrained('digitadoras')->cascadeOnDelete();
            $table->timestamp('fecha_digitadora')->nullable();
            // $table->foreignId('benefit_id')->constrained('benefits')->cascadeOnDelete();
            $table->timestamp('fecha_benefit')->nullable();
            // $table->foreignId('procesador_id')->constrained('procesadores')->cascadeOnDelete();
            $table->string('cobertura_ant');
            $table->integer('codigo_anterior');
            $table->string('ultimo_agente');
            $table->date('fecha_retiro');
            $table->string('agente');
            $table->date('inicio_cobertura');
            $table->date('fin_cobertura');
            $table->string('imagen');
            $table->text('nota_benefit');
            $table->text('nota_procesador');
            $table->text('nota_digitadora');
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
