<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TÍTULO: Extensión longitudinal IADL aprobada por la propietaria el 17/10/2026.
 * CAPA: Infrastructure - Persistencia.
 * RESPONSABILIDAD: Incorporar exclusivamente el diccionario físico de PR #16 (195a39cd).
 * ENTRADAS: Esquema V2 vigente; una observación por actividad, residente y momento.
 * SALIDAS: Tabla con autoría, contexto, corrección append-only y FK restrictivas.
 * RESTRICCIONES: No acredita C03, cambia otras tablas ni habilita captura/pantallas clínicas.
 * TRAZABILIDAD: DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md; inventario 73 operativas.
 */
return new class extends Migration
{
    /** Crea la extensión aprobada; no consulta ni modifica registros existentes. */
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
            $table->string('actividad', 30);
            $table->string('oportunidad', 25);
            $table->string('desempeno', 25);
            $table->string('tipo_ayuda', 30);
            $table->string('causa_ayuda', 30);
            $table->string('nivel_previo', 25);
            $table->string('fuente', 25);
            $table->string('estado', 20)->default('REGISTRADO');
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
        Schema::table('controles_funcionales_iadl', function (Blueprint $table): void {
            $table->foreign('cod_control_anterior')->references('cod_control_iadl')->on('controles_funcionales_iadl')->restrictOnDelete();
        });
    }

    /** Revierte exclusivamente la extensión en un destino autorizado; no se ejecuta contra producción en esta tarea. */
    public function down(): void
    {
        Schema::dropIfExists('controles_funcionales_iadl');
    }
};
