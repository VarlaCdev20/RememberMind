<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->string('estado_operativo', 30)->default('EN_CENTRO')->after('cod_est_adul');
            $table->text('motivo_estado_operativo')->nullable()->after('estado_operativo');
            $table->timestamp('estado_operativo_desde')->nullable()->after('motivo_estado_operativo');
        });

        Schema::create('historial_estado_operativo', function (Blueprint $table) {
            $table->string('cod_estado_operativo', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->text('motivo');
            $table->timestamp('fecha_hora');
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();
            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['cod_am', 'fecha_hora']);
        });

        Schema::create('recepciones_turno', function (Blueprint $table) {
            $table->string('cod_recepcion', 20)->primary();
            $table->string('cod_turno', 20);
            $table->string('cod_usuario', 20);
            $table->timestamp('fecha_hora_recepcion');
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos_enfermeria')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('cod_usuario')->references('cod_usu')->on('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->unique(['cod_turno', 'cod_usuario', 'fecha_hora_recepcion'], 'uq_recepcion_turno_usuario_fecha');
        });

        Schema::create('dispositivos_residente', function (Blueprint $table) {
            $table->string('cod_dispositivo', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('tipo', 50);
            $table->string('ubicacion', 120)->nullable();
            $table->timestamp('fecha_colocacion')->nullable();
            $table->timestamp('fecha_retiro')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->text('indicacion')->nullable();
            $table->text('observacion')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();
            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['cod_am', 'estado']);
        });

        Schema::create('registros_cuidados', function (Blueprint $table) {
            $table->string('cod_registro_cuidado', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_turno', 20)->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->string('tipo', 30);
            $table->timestamp('fecha_hora_evento');
            $table->string('estado', 20)->default('FIRMADO');
            $table->string('subtipo', 50)->nullable();
            $table->string('estado_general', 30)->nullable();
            $table->string('conciencia', 30)->nullable();
            $table->string('cognicion', 30)->nullable();
            $table->string('conducta', 30)->nullable();
            $table->string('respiracion', 30)->nullable();
            $table->unsignedSmallInteger('porcentaje')->nullable();
            $table->unsignedInteger('cantidad_ml')->nullable();
            $table->string('nivel_ayuda', 30)->nullable();
            $table->string('tolerancia', 30)->nullable();
            $table->string('resultado', 30)->nullable();
            $table->string('consistencia', 50)->nullable();
            $table->boolean('es_continente')->nullable();
            $table->boolean('presenta_dificultad')->nullable();
            $table->boolean('presenta_dolor')->nullable();
            $table->boolean('usa_dispositivo')->nullable();
            $table->string('ayuda_tecnica', 80)->nullable();
            $table->string('cambio_respecto_basal', 30)->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->unsignedSmallInteger('cantidad_despertares')->nullable();
            $table->string('calidad', 30)->nullable();
            $table->boolean('deambulacion_nocturna')->nullable();
            $table->boolean('agitacion')->nullable();
            $table->unsignedSmallInteger('dolor')->nullable();
            $table->text('motivo')->nullable();
            $table->text('observacion')->nullable();
            $table->string('rectifica_a', 20)->nullable();
            $table->timestamps();
            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos_enfermeria')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['cod_am', 'fecha_hora_evento']);
            $table->index(['tipo', 'fecha_hora_evento']);
        });

        // PostgreSQL necesita que la clave primaria de la tabla ya exista antes
        // de crear una referencia hacia la misma tabla.
        Schema::table('registros_cuidados', function (Blueprint $table) {
            $table->foreign('rectifica_a')
                ->references('cod_registro_cuidado')
                ->on('registros_cuidados')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('incidentes_residente', function (Blueprint $table) {
            $table->string('cod_incidente', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_turno', 20)->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamp('fecha_hora_evento');
            $table->string('tipo', 40);
            $table->string('lugar', 120);
            $table->string('actividad_previa', 200)->nullable();
            $table->boolean('fue_presenciado')->default(false);
            $table->string('testigo', 200)->nullable();
            $table->text('descripcion');
            $table->unsignedSmallInteger('dolor')->nullable();
            $table->boolean('lesion')->default(false);
            $table->string('movilidad_posterior', 50)->nullable();
            $table->boolean('cambio_cognitivo')->default(false);
            $table->boolean('medico_informado')->default(false);
            $table->boolean('familiar_informado')->default(false);
            $table->boolean('requiere_seguimiento')->default(true);
            $table->string('estado', 30)->default('ABIERTO');
            $table->timestamps();
            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos_enfermeria')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['cod_am', 'fecha_hora_evento']);
        });

        Schema::create('lesiones_residente', function (Blueprint $table) {
            $table->string('cod_lesion', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_incidente', 20)->nullable();
            $table->string('tipo', 50);
            $table->string('zona_corporal', 120);
            $table->string('lateralidad', 20)->nullable();
            $table->string('estado', 30)->default('ACTIVA');
            $table->timestamp('fecha_deteccion');
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();
            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('cod_incidente')->references('cod_incidente')->on('incidentes_residente')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['cod_am', 'estado']);
        });

        Schema::create('seguimientos_lesion', function (Blueprint $table) {
            $table->string('cod_seguimiento_lesion', 20)->primary();
            $table->string('cod_lesion', 20);
            $table->timestamp('fecha_hora_evento');
            $table->decimal('largo_cm', 6, 2)->nullable();
            $table->decimal('ancho_cm', 6, 2)->nullable();
            $table->decimal('profundidad_cm', 6, 2)->nullable();
            $table->boolean('es_medible')->default(true);
            $table->unsignedSmallInteger('dolor')->nullable();
            $table->string('exudado', 50)->nullable();
            $table->string('piel_circundante', 120)->nullable();
            $table->text('aspecto')->nullable();
            $table->text('accion_realizada')->nullable();
            $table->text('observacion')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();
            $table->foreign('cod_lesion')->references('cod_lesion')->on('lesiones_residente')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['cod_lesion', 'fecha_hora_evento']);
        });

        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            $table->string('posicion', 30)->nullable()->after('dolor');
            $table->boolean('usa_oxigeno')->default(false)->after('posicion');
            $table->boolean('valor_atipico_confirmado')->default(false)->after('usa_oxigeno');
        });

        Schema::table('medicacion_adulto', function (Blueprint $table) {
            $table->boolean('es_prn')->default(false)->after('frecuencia');
            $table->text('condicion_prn')->nullable()->after('es_prn');
            $table->unsignedSmallInteger('intervalo_horas')->nullable()->after('hora_programada');
        });

        Schema::table('administracion_medicacion', function (Blueprint $table) {
            $table->string('resultado', 30)->nullable()->after('administrado');
            $table->text('motivo_prn')->nullable()->after('resultado');
            $table->text('valoracion_previa')->nullable()->after('motivo_prn');
            $table->unsignedSmallInteger('intensidad_previa')->nullable()->after('valoracion_previa');
            $table->boolean('requiere_reevaluacion')->default(false)->after('intensidad_previa');
            $table->timestamp('fecha_hora_reevaluacion')->nullable()->after('requiere_reevaluacion');
            $table->unique(['cod_med_adulto', 'fecha', 'hora_programada'], 'uq_dosis_programada_administracion');
        });
    }

    public function down(): void
    {
        Schema::table('administracion_medicacion', function (Blueprint $table) {
            $table->dropUnique('uq_dosis_programada_administracion');
            $table->dropColumn(['resultado', 'motivo_prn', 'valoracion_previa', 'intensidad_previa', 'requiere_reevaluacion', 'fecha_hora_reevaluacion']);
        });
        Schema::table('medicacion_adulto', fn (Blueprint $table) => $table->dropColumn(['es_prn', 'condicion_prn', 'intervalo_horas']));
        Schema::table('signos_vitales_adulto', fn (Blueprint $table) => $table->dropColumn(['posicion', 'usa_oxigeno', 'valor_atipico_confirmado']));
        Schema::dropIfExists('seguimientos_lesion');
        Schema::dropIfExists('lesiones_residente');
        Schema::dropIfExists('incidentes_residente');
        Schema::dropIfExists('registros_cuidados');
        Schema::dropIfExists('dispositivos_residente');
        Schema::dropIfExists('recepciones_turno');
        Schema::dropIfExists('historial_estado_operativo');
        Schema::table('adulto_mayor', fn (Blueprint $table) => $table->dropColumn(['estado_operativo', 'motivo_estado_operativo', 'estado_operativo_desde']));
    }
};
