<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 22 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_condiciones_regla', function (Blueprint $table): void {
            $table->string('cod_evaluacion_condicion', 20);
            $table->string('cod_evaluacion_regla', 20);
            $table->string('cod_condicion_regla', 20);
            $table->string('estado_condicion', 20);
            $table->dateTime('fecha_hora_evaluacion');
            $table->primary(['cod_evaluacion_condicion'], 'se22_pk');
            $table->unique(['cod_evaluacion_regla', 'cod_condicion_regla'], 'se22_u1');
            $table->index(['cod_condicion_regla'], 'se22_i1');
            $table->index(['estado_condicion'], 'se22_i2');
            $table->foreign(['cod_evaluacion_regla'], 'se22_f1')
                ->references(['cod_evaluacion_regla'])->on('evaluaciones_reglas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_condicion_regla'], 'se22_f2')
                ->references(['cod_condicion_regla'])->on('condiciones_regla_experta')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_condiciones_regla');
    }
};
