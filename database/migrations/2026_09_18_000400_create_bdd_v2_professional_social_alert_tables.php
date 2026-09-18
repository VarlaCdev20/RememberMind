<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones_psicologicas', function (Blueprint $table) {
            $table->string('cod_valoracion_psicologica', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20);
            $table->dateTime('fecha_hora');
            $table->string('estado_animo', 50)->nullable();
            $table->string('afecto', 50)->nullable();
            $table->string('ansiedad', 50)->nullable();
            $table->string('apatia', 50)->nullable();
            $table->text('percepcion')->nullable();
            $table->text('conducta')->nullable();
            $table->text('comunicacion')->nullable();
            $table->text('interaccion_social')->nullable();
            $table->text('impresion_cognitiva')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $this->residentPersonalAttentionForeigns($table);
        });

        Schema::create('valoraciones_nutricionales', function (Blueprint $table) {
            $table->string('cod_valoracion_nutricional', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_medicion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado_nutricional', 60)->nullable();
            $table->string('apetito', 40)->nullable();
            $table->string('deglucion', 40)->nullable();
            $table->string('riesgo_desnutricion', 40)->nullable();
            $table->string('necesidad_asistencia', 40)->nullable();
            $table->decimal('requerimiento_hidrico', 10, 2)->nullable();
            $table->text('restricciones_alimentarias')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $this->residentPersonalAttentionForeigns($table);
            $table->foreign('cod_medicion')->references('cod_medicion')->on('mediciones_antropometricas')->restrictOnDelete();
        });

        Schema::create('valoraciones_funcionales', function (Blueprint $table) {
            $table->string('cod_valoracion_funcional', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20);
            $table->dateTime('fecha_hora');
            foreach (['marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia'] as $column) {
                $table->string($column, 40)->nullable();
            }
            foreach (['alimentacion_autonoma', 'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma'] as $column) {
                $table->string($column, 30)->nullable();
            }
            $table->boolean('necesita_supervision')->nullable();
            $table->string('nivel_dependencia', 40)->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $this->residentPersonalAttentionForeigns($table);
        });

        Schema::create('actividades', function (Blueprint $table) {
            $table->string('cod_actividad', 20)->primary();
            $table->string('cod_area', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo', 60);
            $table->string('nombre', 160);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_hora');
            $table->unsignedSmallInteger('duracion_minutos')->nullable();
            $table->string('lugar', 120)->nullable();
            $table->unsignedSmallInteger('cupo')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('seguimientos_pedagogicos', function (Blueprint $table) {
            $table->string('cod_seguimiento_pedagogico', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_actividad', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('atencion', 40)->nullable();
            $table->string('comprension_instrucciones', 40)->nullable();
            $table->string('ejecucion_tarea', 40)->nullable();
            $table->string('reconocimiento', 40)->nullable();
            $table->string('orientacion', 40)->nullable();
            $table->string('participacion', 40)->nullable();
            $table->string('interaccion', 40)->nullable();
            $table->boolean('cambio_desempeno')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_actividad')->references('cod_actividad')->on('actividades')->restrictOnDelete();
        });

        Schema::create('participantes_actividad', function (Blueprint $table) {
            $table->string('cod_participante', 20)->primary();
            $table->string('cod_actividad', 20);
            $table->string('cod_residente', 20);
            $table->string('asistencia', 30)->nullable();
            $table->string('nivel_participacion', 40)->nullable();
            $table->string('desempeno', 40)->nullable();
            $table->text('observacion')->nullable();
            $table->unique(['cod_actividad', 'cod_residente']);
            $table->foreign('cod_actividad')->references('cod_actividad')->on('actividades')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
        });

        Schema::create('visitas', function (Blueprint $table) {
            $table->string('cod_visita', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_contacto', 20);
            $table->string('cod_usuario_autorizacion', 20);
            $table->dateTime('fecha_hora_programada')->nullable();
            $table->dateTime('fecha_hora_ingreso')->nullable();
            $table->dateTime('fecha_hora_salida')->nullable();
            $table->string('motivo', 160)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
            $table->foreign('cod_usuario_autorizacion')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('alertas', function (Blueprint $table) {
            $table->string('cod_alerta', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal_responsable', 20)->nullable();
            $table->string('tipo', 60);
            $table->string('prioridad', 20);
            $table->string('modulo', 60)->nullable();
            $table->string('cod_registro', 20)->nullable();
            $table->string('titulo', 180);
            $table->text('descripcion');
            $table->dateTime('fecha_hora');
            $table->dateTime('fecha_hora_limite')->nullable();
            $table->string('generacion', 30);
            $table->string('estado', 20);
            $table->index(['cod_residente', 'estado', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal_responsable')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('eventos_alerta', function (Blueprint $table) {
            $table->string('cod_evento_alerta', 20)->primary();
            $table->string('cod_alerta', 20);
            $table->string('cod_usuario', 20);
            $table->string('tipo_evento', 50);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('descripcion')->nullable();
            $table->foreign('cod_alerta')->references('cod_alerta')->on('alertas')->restrictOnDelete();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    private function residentPersonalAttentionForeigns(Blueprint $table): void
    {
        $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
        $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
    }

    public function down(): void
    {
        foreach ([
            'eventos_alerta', 'alertas', 'visitas', 'participantes_actividad',
            'seguimientos_pedagogicos', 'actividades', 'valoraciones_funcionales',
            'valoraciones_nutricionales', 'valoraciones_psicologicas',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
