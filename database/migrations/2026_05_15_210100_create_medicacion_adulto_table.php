<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicacion_adulto', function (Blueprint $table) {
            $table->string('cod_med_adulto', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('nombre_medicamento', 200);
            $table->string('dosis', 100);
            $table->string('frecuencia', 100);
            $table->string('via_administracion', 80)->nullable();
            $table->time('hora_programada')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('medico_indica', 200)->nullable();
            $table->string('documento_receta', 20)->nullable();
            $table->string('estado', 30)->default('ACTIVO');
            $table->text('observacion')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('documento_receta')
                ->references('cod_doc_am')->on('documentos_adulto_mayor')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicacion_adulto');
    }
};
