<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('documentos_usuarios', 'tipo_documento')) $table->string('tipo_documento', 100)->nullable()->after('cod_tipo_doc');
            if (! Schema::hasColumn('documentos_usuarios', 'nombre_documento')) $table->string('nombre_documento', 150)->nullable()->after('tipo_documento');
            if (! Schema::hasColumn('documentos_usuarios', 'archivo')) $table->string('archivo', 255)->nullable()->after('nombre_original');
            if (! Schema::hasColumn('documentos_usuarios', 'mime_type')) $table->string('mime_type', 120)->nullable()->after('archivo');
            if (! Schema::hasColumn('documentos_usuarios', 'extension')) $table->string('extension', 20)->nullable()->after('mime_type');
            if (! Schema::hasColumn('documentos_usuarios', 'tamanio')) $table->unsignedBigInteger('tamanio')->nullable()->after('extension');
            if (! Schema::hasColumn('documentos_usuarios', 'fecha_emision')) $table->date('fecha_emision')->nullable()->after('fecha_subida');
            if (! Schema::hasColumn('documentos_usuarios', 'motivo_observacion')) $table->text('motivo_observacion')->nullable()->after('observaciones');
            if (! Schema::hasColumn('documentos_usuarios', 'subido_por')) $table->string('subido_por', 20)->nullable()->after('motivo_observacion');
            if (! Schema::hasColumn('documentos_usuarios', 'reemplaza_a')) $table->string('reemplaza_a', 20)->nullable()->after('fecha_validacion');
            if (! Schema::hasColumn('documentos_usuarios', 'creado_por')) $table->string('creado_por', 20)->nullable()->after('reemplaza_a');
            if (! Schema::hasColumn('documentos_usuarios', 'actualizado_por')) $table->string('actualizado_por', 20)->nullable()->after('creado_por');
        });

        Schema::table('obs_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('obs_adulto', 'tipo_obs')) $table->string('tipo_obs', 100)->nullable()->after('fecha');
            if (! Schema::hasColumn('obs_adulto', 'descripcion')) $table->text('descripcion')->nullable()->after('tipo_obs');
            if (! Schema::hasColumn('obs_adulto', 'registrado_por')) $table->string('registrado_por', 20)->nullable()->after('creado_por');
            if (! Schema::hasColumn('obs_adulto', 'nivel_importancia')) $table->string('nivel_importancia', 50)->nullable()->after('nivel_riesgo');
            if (! Schema::hasColumn('obs_adulto', 'deleted_at')) $table->softDeletes();
        });

        Schema::table('evaluaciones_geriatricas', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'registrado_por')) $table->string('registrado_por', 20)->nullable()->after('cod_instrumento');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'hora_eval')) $table->time('hora_eval')->nullable()->after('fecha_eval');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'puntaje_total')) $table->decimal('puntaje_total', 8, 2)->nullable()->after('puntaje');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'categoria_resultado')) $table->string('categoria_resultado', 150)->nullable()->after('resultado_cualitativo');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'nivel_alerta')) $table->string('nivel_alerta', 30)->nullable()->after('categoria_resultado');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'nivel_riesgo')) $table->string('nivel_riesgo', 30)->nullable()->after('nivel_alerta');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'estado_eval')) $table->string('estado_eval', 30)->nullable()->after('estado');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'datos_formulario')) $table->json('datos_formulario')->nullable()->after('observaciones');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'motivo_anulacion')) $table->text('motivo_anulacion')->nullable()->after('datos_formulario');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'anulado_por')) $table->string('anulado_por', 20)->nullable()->after('motivo_anulacion');
            if (! Schema::hasColumn('evaluaciones_geriatricas', 'anulado_en')) $table->timestamp('anulado_en')->nullable()->after('anulado_por');
        });

        Schema::table('habitaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('habitaciones', 'ubicacion')) $table->string('ubicacion', 150)->nullable()->after('tipo_habitacion');
            if (! Schema::hasColumn('habitaciones', 'observacion')) $table->text('observacion')->nullable()->after('observaciones');
        });

        Schema::table('camas', function (Blueprint $table) {
            if (! Schema::hasColumn('camas', 'observacion')) $table->text('observacion')->nullable()->after('observaciones');
        });

        Schema::table('voluntarios', function (Blueprint $table) {
            if (! Schema::hasColumn('voluntarios', 'fecha_ing')) $table->date('fecha_ing')->nullable()->after('fecha_nac');
            if (! Schema::hasColumn('voluntarios', 'disponibilidad_inicial')) $table->string('disponibilidad_inicial', 150)->nullable()->after('observaciones');
            if (! Schema::hasColumn('voluntarios', 'area_apoyo_preferente')) $table->string('area_apoyo_preferente', 150)->nullable()->after('disponibilidad_inicial');
            if (! Schema::hasColumn('voluntarios', 'archivado_en')) $table->timestamp('archivado_en')->nullable()->after('area_apoyo_preferente');
        });

        Schema::table('disponibilidad_voluntarios', function (Blueprint $table) {
            if (! Schema::hasColumn('disponibilidad_voluntarios', 'observaciones')) $table->text('observaciones')->nullable()->after('obser');
        });

        Schema::table('horarios_personal_salud', function (Blueprint $table) {
            if (! Schema::hasColumn('horarios_personal_salud', 'cod_per_sal')) $table->string('cod_per_sal', 20)->nullable()->after('cod_usu');
        });

        Schema::table('horarios_personal_admin', function (Blueprint $table) {
            if (! Schema::hasColumn('horarios_personal_admin', 'cod_per_adm')) $table->string('cod_per_adm', 20)->nullable()->after('cod_usu');
        });

        Schema::table('asistencia_voluntarios', function (Blueprint $table) {
            if (! Schema::hasColumn('asistencia_voluntarios', 'observaciones')) $table->text('observaciones')->nullable()->after('novedades_observaciones');
        });
    }

    public function down(): void
    {
        // Compatibilidad no destructiva: no eliminamos columnas para evitar perdida de datos.
    }
};
