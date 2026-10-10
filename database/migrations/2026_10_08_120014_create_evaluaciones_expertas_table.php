<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 14 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_expertas', function (Blueprint $table): void {
            $table->string('cod_evaluacion_experta', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('cod_personal_solicitante', 20)->nullable();
            $table->string('origen_activacion', 30);
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin')->nullable();
            $table->string('estado_ejecucion', 30);
            $table->text('motivo_activacion')->nullable();
            $table->text('observacion')->nullable();
            $table->primary(['cod_evaluacion_experta'], 'se14_pk');
            $table->index(['cod_residente', 'fecha_hora_inicio'], 'se14_i1');
            $table->index(['cod_version_modelo', 'fecha_hora_inicio'], 'se14_i2');
            $table->index(['cod_personal_solicitante'], 'se14_i3');
            $table->index(['estado_ejecucion'], 'se14_i4');
            $table->foreign(['cod_residente'], 'se14_f1')
                ->references(['cod_residente'])->on('residentes')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_version_modelo'], 'se14_f2')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_personal_solicitante'], 'se14_f3')
                ->references(['cod_personal'])->on('personal')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_expertas');
    }
};
