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
        Schema::create('digitadores_coordinadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('digitador_id');
            $table->unsignedBigInteger('coordinador_id');
            $table->timestamps();

            $table->foreignId('coordinador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('digitador_id')->constrained('users')->cascadeOnDelete();

            // $table->foreign('digitador_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('coordinador_id')->references('id')->on('users')->onDelete('cascade');

            // Esto garantiza que la combinación de digitador y coordinador sea única.
            $table->unique(['digitador_id', 'coordinador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digitadores_coordinadores');
    }
};
