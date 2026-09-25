<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_clinicos', function (Blueprint $table) {
            $table->string('cod_documento_clinico', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_estudio', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->string('cod_personal', 20)->nullable();
            $table->string('tipo_documento', 60);
            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            $table->string('ruta_archivo', 500);
            $table->string('formato', 30);
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('hash_archivo', 128);
            $table->dateTime('fecha_hora');
            $table->string('origen', 20)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_estudio')->references('cod_estudio')->on('estudios_clinicos')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_clinicos');
    }
};
