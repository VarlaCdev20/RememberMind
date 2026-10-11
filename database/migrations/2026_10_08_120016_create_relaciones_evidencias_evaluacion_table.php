<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 16 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relaciones_evidencias_evaluacion', function (Blueprint $table): void {
            $table->string('cod_relacion_evidencia', 20);
            $table->string('cod_evidencia_origen', 20);
            $table->string('cod_evidencia_destino', 20);
            $table->string('tipo_relacion', 50);
            $table->string('estado', 20);
            $table->text('justificacion')->nullable();
            $table->dateTime('fecha_hora_creacion');
            $table->primary(['cod_relacion_evidencia'], 'se16_pk');
            $table->unique(['cod_evidencia_origen', 'cod_evidencia_destino', 'tipo_relacion'], 'se16_u1');
            $table->index(['cod_evidencia_destino'], 'se16_i1');
            $table->index(['tipo_relacion'], 'se16_i2');
            $table->foreign(['cod_evidencia_origen'], 'se16_f1')
                ->references(['cod_evidencia_evaluacion'])->on('evidencias_evaluacion')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_evidencia_destino'], 'se16_f2')
                ->references(['cod_evidencia_evaluacion'])->on('evidencias_evaluacion')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relaciones_evidencias_evaluacion');
    }
};
