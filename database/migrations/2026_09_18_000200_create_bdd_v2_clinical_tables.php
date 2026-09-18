<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_clinicas', function (Blueprint $table) {
            $table->string('cod_nota', 20)->primary();
            $table->string('cod_atencion', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_nota_anterior', 20)->nullable();
            $table->string('tipo_nota', 50);
            $table->text('contenido');
            $table->dateTime('fecha_hora');
            $table->text('motivo_correccion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_nota_anterior')->references('cod_nota')->on('notas_clinicas')->restrictOnDelete();
        });

        Schema::create('antecedentes_clinicos', function (Blueprint $table) {
            $table->string('cod_antecedente', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_antecedente', 60);
            $table->text('descripcion');
            $table->date('fecha_referencia')->nullable();
            $table->string('fuente_informacion', 80)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->string('cod_diagnostico', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_personal', 20);
            $table->string('codigo_clinico', 30)->nullable();
            $table->string('nombre', 160);
            $table->string('tipo', 50)->nullable();
            $table->string('certeza', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('alergias', function (Blueprint $table) {
            $table->string('cod_alergia', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo', 40)->nullable();
            $table->string('sustancia', 120);
            $table->text('reaccion')->nullable();
            $table->string('gravedad', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('seguros_residente', function (Blueprint $table) {
            $table->string('cod_seguro', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('entidad', 120);
            $table->string('plan', 100)->nullable();
            $table->string('numero_afiliacion', 80)->nullable();
            $table->string('titular', 160)->nullable();
            $table->text('cobertura')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
        });

        Schema::create('dispositivos_clinicos', function (Blueprint $table) {
            $table->string('cod_dispositivo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo', 60);
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 120)->nullable();
            $table->dateTime('fecha_colocacion')->nullable();
            $table->dateTime('fecha_retiro')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('signos_vitales', function (Blueprint $table) {
            $table->string('cod_signo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->decimal('presion_sistolica', 5, 2)->nullable();
            $table->decimal('presion_diastolica', 5, 2)->nullable();
            $table->decimal('frecuencia_cardiaca', 6, 2)->nullable();
            $table->decimal('frecuencia_respiratoria', 6, 2)->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->decimal('saturacion_oxigeno', 5, 2)->nullable();
            $table->decimal('glucemia', 8, 2)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        Schema::create('valoraciones_dolor', function (Blueprint $table) {
            $table->string('cod_valoracion_dolor', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->unsignedTinyInteger('intensidad')->nullable();
            $table->string('ubicacion', 120)->nullable();
            $table->string('tipo_dolor', 60)->nullable();
            $table->string('duracion', 80)->nullable();
            $table->text('desencadenante')->nullable();
            $table->text('intervencion')->nullable();
            $table->text('respuesta')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        Schema::create('mediciones_antropometricas', function (Blueprint $table) {
            $table->string('cod_medicion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->dateTime('fecha_hora');
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('talla', 5, 2)->nullable();
            $table->decimal('imc', 5, 2)->nullable();
            $table->decimal('perimetro_braquial', 6, 2)->nullable();
            $table->decimal('perimetro_pantorrilla', 6, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('componentes_estudio', function (Blueprint $table) {
            $table->string('cod_componente', 20)->primary();
            $table->string('cod_tipo_estudio', 20);
            $table->string('nombre', 120);
            $table->string('unidad_referencia', 40)->nullable();
            $table->string('tipo_resultado', 30);
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->foreign('cod_tipo_estudio')->references('cod_tipo_estudio')->on('tipos_estudio_clinico')->restrictOnDelete();
        });

        Schema::create('estudios_clinicos', function (Blueprint $table) {
            $table->string('cod_estudio', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_tipo_estudio', 20);
            $table->string('cod_personal', 20);
            $table->text('motivo')->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_solicitud');
            $table->dateTime('fecha_realizacion')->nullable();
            $table->string('centro_medico', 160)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_solicitud']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_tipo_estudio')->references('cod_tipo_estudio')->on('tipos_estudio_clinico')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('resultados_estudio', function (Blueprint $table) {
            $table->string('cod_resultado_estudio', 20)->primary();
            $table->string('cod_estudio', 20);
            $table->string('cod_componente', 20);
            $table->decimal('valor_numerico', 18, 6)->nullable();
            $table->text('valor_texto')->nullable();
            $table->string('unidad', 40)->nullable();
            $table->string('rango_referencia', 120)->nullable();
            $table->string('clasificacion', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->unique(['cod_estudio', 'cod_componente']);
            $table->foreign('cod_estudio')->references('cod_estudio')->on('estudios_clinicos')->restrictOnDelete();
            $table->foreign('cod_componente')->references('cod_componente')->on('componentes_estudio')->restrictOnDelete();
        });

        Schema::create('informes_estudio', function (Blueprint $table) {
            $table->string('cod_informe_estudio', 20)->primary();
            $table->string('cod_estudio', 20);
            $table->string('cod_personal', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('hallazgos')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('origen', 20);
            $table->string('profesional_externo', 160)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_estudio')->references('cod_estudio')->on('estudios_clinicos')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('documentos_clinicos', function (Blueprint $table) {
            $table->string('cod_documento_clinico', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_estudio', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->string('cod_personal', 20)->nullable();
            $table->string('tipo_documento', 60);
            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            $table->string('ruta_archivo', 500);
            $table->string('formato', 30);
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('hash_archivo', 128);
            $table->dateTime('fecha_hora');
            $table->string('origen', 20)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_estudio')->references('cod_estudio')->on('estudios_clinicos')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('derivaciones', function (Blueprint $table) {
            $table->string('cod_derivacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_area_solicitante', 20);
            $table->string('cod_area_receptora', 20);
            $table->string('cod_personal_solicitante', 20);
            $table->string('cod_personal_receptor', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->text('motivo');
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('respuesta')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_area_solicitante')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_area_receptora')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal_solicitante')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_personal_receptor')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        Schema::create('incidentes', function (Blueprint $table) {
            $table->string('cod_incidente', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('tipo_incidente', 60);
            $table->string('gravedad', 30)->nullable();
            $table->string('lugar', 120)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('descripcion');
            $table->text('medida_inmediata')->nullable();
            $table->boolean('requiere_medico');
            $table->boolean('requiere_derivacion');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });

        Schema::create('indicaciones_clinicas', function (Blueprint $table) {
            $table->string('cod_indicacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_indicacion', 40);
            $table->text('descripcion');
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('asignaciones_residente_jornada', function (Blueprint $table) {
            $table->string('cod_asignacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->string('nivel_supervision', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('controles_cognitivos', function (Blueprint $table) {
            $table->string('cod_control_cognitivo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            foreach (['orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo', 'memoria_reciente', 'memoria_remota', 'atencion', 'comprension', 'lenguaje'] as $column) {
                $table->string($column, 30)->nullable();
            }
            foreach (['sigue_instrucciones', 'repite_preguntas', 'olvida_indicaciones', 'reconoce_personas', 'reconoce_entorno', 'confusion', 'cambio_cognitivo'] as $column) {
                $table->boolean($column)->nullable();
            }
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        Schema::create('registros_conductuales', function (Blueprint $table) {
            $table->string('cod_registro_conductual', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado_animo', 40)->nullable();
            foreach (['apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion'] as $column) {
                $table->boolean($column)->nullable();
            }
            $table->string('participacion', 30)->nullable();
            $table->boolean('cambio_conducta')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('intervencion')->nullable();
            $table->text('respuesta')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });

        Schema::create('registros_sueno', function (Blueprint $table) {
            $table->string('cod_registro_sueno', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->date('fecha');
            $table->decimal('horas_sueno', 4, 2)->nullable();
            $table->smallInteger('despertares')->nullable();
            $table->boolean('insomnio')->nullable();
            $table->boolean('somnolencia_diurna')->nullable();
            $table->boolean('agitacion_nocturna')->nullable();
            $table->string('calidad', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });

        $this->createRegistroIngesta();
        $this->createRegistroHidratacion();
        $this->createRegistroEliminacion();
        $this->createRegistroMovilidad();

        Schema::create('heridas', function (Blueprint $table) {
            $table->string('cod_herida', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_herida', 60);
            $table->string('ubicacion', 120);
            $table->string('causa', 120)->nullable();
            $table->string('clasificacion', 60)->nullable();
            $table->dateTime('fecha_hora_identificacion');
            $table->dateTime('fecha_hora_cierre')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::create('curaciones_herida', function (Blueprint $table) {
            $table->string('cod_curacion', 20)->primary();
            $table->string('cod_herida', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->decimal('longitud', 7, 2)->nullable();
            $table->decimal('ancho', 7, 2)->nullable();
            $table->decimal('profundidad', 7, 2)->nullable();
            $table->string('tejido', 80)->nullable();
            $table->string('exudado', 80)->nullable();
            $table->string('olor', 80)->nullable();
            $table->string('dolor', 40)->nullable();
            $table->text('procedimiento');
            $table->text('materiales')->nullable();
            $table->text('respuesta')->nullable();
            $table->text('observacion')->nullable();
            $table->foreign('cod_herida')->references('cod_herida')->on('heridas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });

        Schema::create('pases_turno', function (Blueprint $table) {
            $table->string('cod_pase', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_jornada_saliente', 20);
            $table->string('cod_jornada_entrante', 20);
            $table->string('cod_personal_saliente', 20);
            $table->string('cod_personal_entrante', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('estado_general')->nullable();
            $table->text('resumen');
            $table->text('pendientes')->nullable();
            $table->text('vigilancia')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada_saliente')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_jornada_entrante')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal_saliente')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_personal_entrante')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    private function createRegistroIngesta(): void
    {
        Schema::create('registros_ingesta', function (Blueprint $table) {
            $table->string('cod_ingesta', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20);
            $table->dateTime('fecha_hora');
            $table->string('tipo_comida', 40);
            $table->decimal('porcentaje_consumido', 5, 2)->nullable();
            $table->string('apetito', 30)->nullable();
            $table->string('tolerancia', 30)->nullable();
            $table->boolean('dificultad_deglucion')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $this->residentPersonalJornadaForeigns($table);
        });
    }

    private function createRegistroHidratacion(): void
    {
        Schema::create('registros_hidratacion', function (Blueprint $table) {
            $table->string('cod_hidratacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20);
            $table->dateTime('fecha_hora');
            $table->decimal('cantidad_ml', 8, 2);
            $table->string('tipo_liquido', 60)->nullable();
            $table->string('tolerancia', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $this->residentPersonalJornadaForeigns($table);
        });
    }

    private function createRegistroEliminacion(): void
    {
        Schema::create('registros_eliminacion', function (Blueprint $table) {
            $table->string('cod_eliminacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20);
            $table->dateTime('fecha_hora');
            $table->string('tipo_eliminacion', 30);
            $table->string('cantidad', 40)->nullable();
            $table->string('caracteristica', 120)->nullable();
            $table->string('continencia', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $this->residentPersonalJornadaForeigns($table);
        });
    }

    private function createRegistroMovilidad(): void
    {
        Schema::create('registros_movilidad', function (Blueprint $table) {
            $table->string('cod_movilidad', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('marcha', 40)->nullable();
            $table->string('equilibrio', 40)->nullable();
            $table->string('traslado', 40)->nullable();
            $table->string('tipo_apoyo', 60)->nullable();
            $table->string('dispositivo', 80)->nullable();
            $table->string('fatiga', 30)->nullable();
            $table->string('riesgo_caida', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    private function residentPersonalJornadaForeigns(Blueprint $table): void
    {
        $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
        $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
    }

    public function down(): void
    {
        foreach ([
            'pases_turno', 'curaciones_herida', 'heridas', 'registros_movilidad',
            'registros_eliminacion', 'registros_hidratacion', 'registros_ingesta',
            'registros_sueno', 'registros_conductuales', 'controles_cognitivos',
            'asignaciones_residente_jornada', 'indicaciones_clinicas', 'incidentes',
            'derivaciones', 'documentos_clinicos', 'informes_estudio', 'resultados_estudio',
            'estudios_clinicos', 'componentes_estudio', 'mediciones_antropometricas',
            'valoraciones_dolor', 'signos_vitales', 'dispositivos_clinicos',
            'seguros_residente', 'alergias', 'diagnosticos', 'antecedentes_clinicos',
            'notas_clinicas',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
