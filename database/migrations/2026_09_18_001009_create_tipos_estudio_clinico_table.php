<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_estudio_clinico', function (Blueprint $table) {
            $table->string('cod_tipo_estudio', 20)->primary();
            $table->string('nombre', 120)->unique();
            $table->string('categoria', 50);
            $table->text('descripcion')->nullable();
            $table->boolean('requiere_componentes');
            $table->boolean('requiere_informe');
            $table->string('estado', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_estudio_clinico');
    }
};
