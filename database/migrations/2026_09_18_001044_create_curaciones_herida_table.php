<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curaciones_herida', function (Blueprint $table) {
            $table->string('cod_curacion', 20)->primary();
            $table->string('cod_herida', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->decimal('longitud', 7, 2)->nullable();
            $table->decimal('ancho', 7, 2)->nullable();
            $table->decimal('profundidad', 7, 2)->nullable();
            $table->string('tejido', 80)->nullable();
            $table->string('exudado', 80)->nullable();
            $table->string('olor', 80)->nullable();
            $table->string('dolor', 40)->nullable();
            $table->text('procedimiento');
            $table->text('materiales')->nullable();
            $table->text('respuesta')->nullable();
            $table->text('observacion')->nullable();
            $table->foreign('cod_herida')->references('cod_herida')->on('heridas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curaciones_herida');
    }
};
