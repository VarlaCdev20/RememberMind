<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_actividades_adulto', function (Blueprint $table) {
            $table->string('cod_tipo_act', 20)->primary();
            $table->string('nombre', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->string('estado', 50)->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_actividades_adulto');
    }
};