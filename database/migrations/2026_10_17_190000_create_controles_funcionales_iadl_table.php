<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extensión operativa propuesta IADL-C03, separada del baseline V2.1.
 * Una fila = una actividad instrumental observada/informada en un momento.
 * Append-only: ninguna corrección reemplaza la evidencia histórica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles_funcionales_iadl', function (Blueprint $table): void {
            $table->string('cod_control_iadl', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_usuario_registro', 20);
            $table->string('cod_atencion', 20)->nullable();
            $table->string('cod_control_anterior', 20)->nullable();
            $table->dateTime('fecha_hora');
            // Catálogo cerrado en aplicación; no crear EAV ni una tabla por tarea.
            $table->string('actividad', 30);
            $table->string('oportunidad', 25);
            $table->string('desempeno', 25);
            $table->string('tipo_ayuda', 30);
            $table->string('causa_ayuda', 30);
            $table->string('nivel_previo', 25);
            $table->string('fuente', 25);
            $table->string('estado', 20)->default('REGISTRADO');
            // Nota descriptiva: NUNCA se parsea para inferir independencia.
            $table->text('observacion')->nullable();
            $table->dateTime('registrado_en');
            $table->index(['cod_residente', 'fecha_hora'], 'iadl_residente_fecha_idx');
            $table->index(['cod_residente', 'actividad', 'fecha_hora'], 'iadl_residente_tarea_idx');
            $table->index('cod_personal', 'iadl_personal_idx');
            $table->index('cod_usuario_registro', 'iadl_usuario_idx');
            $table->index('cod_atencion', 'iadl_atencion_idx');
            $table->index('cod_control_anterior', 'iadl_anterior_idx');
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        // PostgreSQL necesita la PK ya instalada antes de crear la autorreferencia.
        Schema::table('controles_funcionales_iadl', function (Blueprint $table): void {
            $table->foreign('cod_control_anterior')->references('cod_control_iadl')->on('controles_funcionales_iadl')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_funcionales_iadl');
    }
};
