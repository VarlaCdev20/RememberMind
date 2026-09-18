<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_cuidado', function (Blueprint $table) {
            $table->string('cod_plan', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_area', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_plan', 50);
            $table->string('nombre', 160);
            $table->text('objetivo_general');
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_hora_apertura');
            $table->dateTime('fecha_hora_cierre')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('intervenciones_cuidado', function (Blueprint $table) {
            $table->string('cod_intervencion', 20)->primary();
            $table->string('cod_plan', 20);
            $table->string('nombre', 160);
            $table->text('descripcion');
            $table->text('objetivo_especifico')->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_plan')->references('cod_plan')->on('planes_cuidado')->restrictOnDelete();
        });

        Schema::create('programaciones_cuidado', function (Blueprint $table) {
            $table->string('cod_programacion', 20)->primary();
            $table->string('cod_intervencion', 20);
            $table->string('cod_turno', 20)->nullable();
            $table->string('frecuencia', 60);
            $table->string('dias_semana', 50)->nullable();
            $table->time('hora_programada')->nullable();
            $table->date('fecha_activacion');
            $table->date('fecha_desactivacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_intervencion')->references('cod_intervencion')->on('intervenciones_cuidado')->restrictOnDelete();
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos')->restrictOnDelete();
        });

        Schema::create('ejecuciones_cuidado', function (Blueprint $table) {
            $table->string('cod_ejecucion', 20)->primary();
            $table->string('cod_intervencion', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->dateTime('fecha_hora_programada')->nullable();
            $table->dateTime('fecha_hora_ejecucion')->nullable();
            $table->string('resultado', 60)->nullable();
            $table->text('motivo_omision')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_intervencion')->references('cod_intervencion')->on('intervenciones_cuidado')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('prescripciones', function (Blueprint $table) {
            $table->string('cod_prescripcion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_medicamento', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_personal_suspension', 20)->nullable();
            $table->decimal('dosis', 10, 3)->nullable();
            $table->string('unidad_dosis', 30)->nullable();
            $table->string('via_administracion', 60);
            $table->string('frecuencia', 80)->nullable();
            $table->text('indicacion')->nullable();
            $table->boolean('segun_necesidad');
            $table->dateTime('fecha_hora_prescripcion');
            $table->dateTime('fecha_hora_suspension')->nullable();
            $table->text('motivo_suspension')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'estado']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_medicamento')->references('cod_medicamento')->on('medicamentos')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_personal_suspension')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('horarios_prescripcion', function (Blueprint $table) {
            $table->string('cod_horario_prescripcion', 20)->primary();
            $table->string('cod_prescripcion', 20);
            $table->time('hora_programada');
            $table->decimal('dosis_programada', 10, 3)->nullable();
            $table->string('dias_semana', 50)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_prescripcion')->references('cod_prescripcion')->on('prescripciones')->restrictOnDelete();
        });

        Schema::create('administraciones_medicacion', function (Blueprint $table) {
            $table->string('cod_administracion', 20)->primary();
            $table->string('cod_prescripcion', 20);
            $table->string('cod_horario_prescripcion', 20)->nullable();
            $table->string('cod_residente', 20);
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->dateTime('fecha_hora_programada')->nullable();
            $table->dateTime('fecha_hora_administracion')->nullable();
            $table->string('resultado', 40);
            $table->decimal('dosis_administrada', 10, 3)->nullable();
            $table->text('motivo_omision')->nullable();
            $table->text('efecto_observado')->nullable();
            $table->text('reaccion_adversa')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora_programada']);
            $table->foreign('cod_prescripcion')->references('cod_prescripcion')->on('prescripciones')->restrictOnDelete();
            $table->foreign('cod_horario_prescripcion')->references('cod_horario_prescripcion')->on('horarios_prescripcion')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('preguntas_instrumento', function (Blueprint $table) {
            $table->string('cod_pregunta', 20)->primary();
            $table->string('cod_instrumento', 20);
            $table->string('codigo', 30);
            $table->text('enunciado');
            $table->string('dominio', 80)->nullable();
            $table->string('tipo_respuesta', 30);
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->foreign('cod_instrumento')->references('cod_instrumento')->on('instrumentos')->restrictOnDelete();
        });

        Schema::create('opciones_pregunta', function (Blueprint $table) {
            $table->string('cod_opcion', 20)->primary();
            $table->string('cod_pregunta', 20);
            $table->string('nombre', 160);
            $table->string('valor', 120)->nullable();
            $table->decimal('puntaje', 8, 2)->nullable();
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->foreign('cod_pregunta')->references('cod_pregunta')->on('preguntas_instrumento')->restrictOnDelete();
        });

        Schema::create('aplicaciones_instrumento', function (Blueprint $table) {
            $table->string('cod_aplicacion', 20)->primary();
            $table->string('cod_instrumento', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->decimal('puntaje_total', 8, 2)->nullable();
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->string('clasificacion', 80)->nullable();
            $table->text('interpretacion')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_instrumento')->references('cod_instrumento')->on('instrumentos')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        Schema::create('respuestas_instrumento', function (Blueprint $table) {
            $table->string('cod_respuesta', 20)->primary();
            $table->string('cod_aplicacion', 20);
            $table->string('cod_pregunta', 20);
            $table->string('cod_opcion', 20)->nullable();
            $table->decimal('valor_numero', 12, 4)->nullable();
            $table->text('valor_texto')->nullable();
            $table->boolean('valor_logico')->nullable();
            $table->decimal('puntaje', 8, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->unique(['cod_aplicacion', 'cod_pregunta']);
            $table->foreign('cod_aplicacion')->references('cod_aplicacion')->on('aplicaciones_instrumento')->restrictOnDelete();
            $table->foreign('cod_pregunta')->references('cod_pregunta')->on('preguntas_instrumento')->restrictOnDelete();
            $table->foreign('cod_opcion')->references('cod_opcion')->on('opciones_pregunta')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        foreach ([
            'respuestas_instrumento', 'aplicaciones_instrumento', 'opciones_pregunta',
            'preguntas_instrumento', 'administraciones_medicacion', 'horarios_prescripcion',
            'prescripciones', 'ejecuciones_cuidado', 'programaciones_cuidado',
            'intervenciones_cuidado', 'planes_cuidado',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
