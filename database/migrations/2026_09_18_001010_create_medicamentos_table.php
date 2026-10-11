<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->string('cod_medicamento', 20)->primary();
            $table->string('nombre_generico', 120);
            $table->string('nombre_comercial', 120)->nullable();
            $table->string('concentracion', 60)->nullable();
            $table->string('forma_farmaceutica', 60)->nullable();
            $table->string('unidad', 30)->nullable();
            $table->string('via_predeterminada', 60)->nullable();
            $table->boolean('control_especial');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
