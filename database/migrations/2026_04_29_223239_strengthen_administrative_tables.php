<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. documentos_adulto_mayor
        if (Schema::hasTable('documentos_adulto_mayor')) {
            Schema::table('documentos_adulto_mayor', function (Blueprint $table) {
                if (!Schema::hasColumn('documentos_adulto_mayor', 'estado')) $table->string('estado')->default('ACTIVO');
                if (!Schema::hasColumn('documentos_adulto_mayor', 'archivado_en')) $table->timestamp('archivado_en')->nullable();
                if (!Schema::hasColumn('documentos_adulto_mayor', 'motivo_archivado')) $table->text('motivo_archivado')->nullable();
                if (!Schema::hasColumn('documentos_adulto_mayor', 'created_at')) $table->timestamps();
            });
        }

        // 2. obs_adulto
        if (Schema::hasTable('obs_adulto')) {
            Schema::table('obs_adulto', function (Blueprint $table) {
                if (!Schema::hasColumn('obs_adulto', 'nivel_importancia')) $table->string('nivel_importancia')->nullable();
                if (!Schema::hasColumn('obs_adulto', 'registrado_por')) $table->string('registrado_por', 20)->nullable();
                if (!Schema::hasColumn('obs_adulto', 'estado_revision')) $table->string('estado_revision')->default('PENDIENTE');
                if (!Schema::hasColumn('obs_adulto', 'created_at')) $table->timestamps();
            });
        }

        // 3. actividades_adulto
        if (Schema::hasTable('actividades_adulto')) {
            Schema::table('actividades_adulto', function (Blueprint $table) {
                if (!Schema::hasColumn('actividades_adulto', 'hora_fin')) $table->time('hora_fin')->nullable();
                if (!Schema::hasColumn('actividades_adulto', 'responsable_tipo')) $table->string('responsable_tipo')->nullable();
                if (!Schema::hasColumn('actividades_adulto', 'responsable_id')) $table->string('responsable_id', 20)->nullable();
                if (!Schema::hasColumn('actividades_adulto', 'created_at')) $table->timestamps();
            });
        }

        // 4. atenciones_adulto
        if (Schema::hasTable('atenciones_adulto')) {
            Schema::table('atenciones_adulto', function (Blueprint $table) {
                if (!Schema::hasColumn('atenciones_adulto', 'responsable_tipo')) $table->string('responsable_tipo')->nullable();
                if (!Schema::hasColumn('atenciones_adulto', 'responsable_id')) $table->string('responsable_id', 20)->nullable();
                if (!Schema::hasColumn('atenciones_adulto', 'created_at')) $table->timestamps();
            });
        }

        // 5. voluntarios
        if (Schema::hasTable('voluntarios')) {
            Schema::table('voluntarios', function (Blueprint $table) {
                if (!Schema::hasColumn('voluntarios', 'archivado_en')) $table->timestamp('archivado_en')->nullable();
                if (!Schema::hasColumn('voluntarios', 'motivo_archivado')) $table->text('motivo_archivado')->nullable();
                if (!Schema::hasColumn('voluntarios', 'created_at')) $table->timestamps();
            });
        }

        // 6. familiares
        if (Schema::hasTable('familiares')) {
            Schema::table('familiares', function (Blueprint $table) {
                if (!Schema::hasColumn('familiares', 'estado')) $table->string('estado')->default('ACTIVO');
                if (!Schema::hasColumn('familiares', 'created_at')) $table->timestamps();
            });
        }

        // 7. personal_salud
        if (Schema::hasTable('personal_salud')) {
            Schema::table('personal_salud', function (Blueprint $table) {
                if (!Schema::hasColumn('personal_salud', 'archivado_en')) $table->timestamp('archivado_en')->nullable();
                if (!Schema::hasColumn('personal_salud', 'motivo_archivado')) $table->text('motivo_archivado')->nullable();
                if (!Schema::hasColumn('personal_salud', 'created_at')) $table->timestamps();
            });
        }

        // 8. personal_admin
        if (Schema::hasTable('personal_admin')) {
            Schema::table('personal_admin', function (Blueprint $table) {
                if (!Schema::hasColumn('personal_admin', 'archivado_en')) $table->timestamp('archivado_en')->nullable();
                if (!Schema::hasColumn('personal_admin', 'motivo_archivado')) $table->text('motivo_archivado')->nullable();
                if (!Schema::hasColumn('personal_admin', 'created_at')) $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se recomienda revertir fortalecimientos para evitar pérdida de datos potenciales
    }
};
