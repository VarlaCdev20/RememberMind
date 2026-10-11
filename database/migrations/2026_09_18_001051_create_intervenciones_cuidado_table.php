<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervenciones_cuidado', function (Blueprint $table) {
            $table->string('cod_intervencion', 20)->primary();
            $table->string('cod_plan', 20);
            $table->string('nombre', 160);
            $table->text('descripcion');
            $table->text('objetivo_especifico')->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_plan')->references('cod_plan')->on('planes_cuidado')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervenciones_cuidado');
    }
};
