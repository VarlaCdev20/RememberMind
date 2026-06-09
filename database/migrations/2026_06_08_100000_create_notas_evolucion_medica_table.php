<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_evolucion_medica', function (Blueprint $table) {
            $table->string('cod_nota', 20)->primary();
            $table->string('cod_am', 20)->index();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->enum('tipo_nota', [
                'EVOLUCION', 'INGRESO', 'EGRESO',
                'INTERCONSULTA', 'URGENCIA', 'PROCEDIMIENTO'
            ])->default('EVOLUCION');

            // SOAP
            $table->text('subjetivo')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('valoracion');
            $table->text('plan');
            $table->text('observaciones')->nullable();

            // Signos vitales opcionales al momento de la nota
            $table->unsignedSmallInteger('pa_sistolica')->nullable();
            $table->unsignedSmallInteger('pa_diastolica')->nullable();
            $table->unsignedSmallInteger('fc')->nullable();
            $table->unsignedSmallInteger('fr')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->unsignedSmallInteger('saturacion')->nullable();
            $table->decimal('glucosa', 5, 1)->nullable();
            $table->decimal('peso', 5, 1)->nullable();

            // Auditoría
            $table->string('registrado_por', 20)->nullable();
            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');
            $table->text('motivo_anulacion')->nullable();
            $table->string('anulado_por', 20)->nullable();
            $table->timestamp('anulado_en')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->nullOnDelete();
            $table->index(['cod_am', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_evolucion_medica');
    }
};
