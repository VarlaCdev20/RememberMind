<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residentes', function (Blueprint $table) {
            $table->string('cod_residente', 20)->primary();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->nullable()->unique();
            $table->string('complemento_documento', 10)->nullable();
            $table->string('expedicion_documento', 20)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('genero', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('nivel_educativo', 80)->nullable();
            $table->string('grupo_sanguineo', 5)->nullable();
            $table->string('factor_rh', 5)->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residentes');
    }
};
