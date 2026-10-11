<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 23 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias_soporte_condicion', function (Blueprint $table): void {
            $table->string('cod_evidencia_soporte_condicion', 20);
            $table->string('cod_evaluacion_condicion', 20);
            $table->string('cod_evidencia_evaluacion', 20);
            $table->primary(['cod_evidencia_soporte_condicion'], 'se23_pk');
            $table->unique(['cod_evaluacion_condicion', 'cod_evidencia_evaluacion'], 'se23_u1');
            $table->index(['cod_evidencia_evaluacion'], 'se23_i1');
            $table->foreign(['cod_evaluacion_condicion'], 'se23_f1')
                ->references(['cod_evaluacion_condicion'])->on('evaluaciones_condiciones_regla')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_evidencia_evaluacion'], 'se23_f2')
                ->references(['cod_evidencia_evaluacion'])->on('evidencias_evaluacion')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias_soporte_condicion');
    }
};
