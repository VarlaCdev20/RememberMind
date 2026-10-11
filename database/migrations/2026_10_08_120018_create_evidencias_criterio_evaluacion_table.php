<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 18 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias_criterio_evaluacion', function (Blueprint $table): void {
            $table->string('cod_evidencia_criterio', 20);
            $table->string('cod_evaluacion_criterio', 20);
            $table->string('cod_evidencia_evaluacion', 20);
            $table->string('rol_en_criterio', 30);
            $table->string('estado_participacion', 30);
            $table->text('justificacion')->nullable();
            $table->dateTime('fecha_hora_vinculacion');
            $table->primary(['cod_evidencia_criterio'], 'se18_pk');
            $table->unique(['cod_evaluacion_criterio', 'cod_evidencia_evaluacion'], 'se18_u1');
            $table->index(['cod_evidencia_evaluacion'], 'se18_i1');
            $table->index(['estado_participacion'], 'se18_i2');
            $table->foreign(['cod_evaluacion_criterio'], 'se18_f1')
                ->references(['cod_evaluacion_criterio'])->on('evaluacion_criterios')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_evidencia_evaluacion'], 'se18_f2')
                ->references(['cod_evidencia_evaluacion'])->on('evidencias_evaluacion')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias_criterio_evaluacion');
    }
};
