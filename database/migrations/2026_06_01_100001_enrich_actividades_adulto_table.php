<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // hora_fin, responsable_tipo, responsable_id y timestamps
        // ya fueron agregados en strengthen_administrative_tables.
        // Solo se agregan los campos nuevos de Fase 1 que no existen.

        Schema::table('actividades_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('actividades_adulto', 'nombre')) {
                $table->string('nombre')->nullable()->after('cod_am');
            }
            if (! Schema::hasColumn('actividades_adulto', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('actividades_adulto', 'objetivo')) {
                $table->text('objetivo')->nullable()->after('descripcion');
            }
            if (! Schema::hasColumn('actividades_adulto', 'lugar')) {
                $table->string('lugar')->nullable()->after('objetivo');
            }
            if (! Schema::hasColumn('actividades_adulto', 'cupo_maximo')) {
                $table->unsignedSmallInteger('cupo_maximo')->nullable()->after('lugar');
            }
            if (! Schema::hasColumn('actividades_adulto', 'materiales')) {
                $table->text('materiales')->nullable()->after('cupo_maximo');
            }
            if (! Schema::hasColumn('actividades_adulto', 'resultado_general')) {
                $table->text('resultado_general')->nullable()->after('obs');
            }
            if (! Schema::hasColumn('actividades_adulto', 'nivel_cumplimiento')) {
                $table->string('nivel_cumplimiento', 20)->nullable()->after('resultado_general');
                // Valores: ALTO, MEDIO, BAJO, NO_EVALUADO
            }
            if (! Schema::hasColumn('actividades_adulto', 'incidencias')) {
                $table->text('incidencias')->nullable()->after('nivel_cumplimiento');
            }
            if (! Schema::hasColumn('actividades_adulto', 'recomendaciones')) {
                $table->text('recomendaciones')->nullable()->after('incidencias');
            }
            if (! Schema::hasColumn('actividades_adulto', 'color')) {
                $table->string('color', 10)->nullable()->after('estado');
                // Hex corto para UI, ej: #BC6C25
            }
            if (! Schema::hasColumn('actividades_adulto', 'created_by')) {
                $table->string('created_by', 20)->nullable()->after('updated_at');
                // FK soft hacia users.cod_usu (sin constraint para flexibilidad histórica)
            }
            if (! Schema::hasColumn('actividades_adulto', 'updated_by')) {
                $table->string('updated_by', 20)->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('actividades_adulto', function (Blueprint $table) {
            $columns = [
                'nombre', 'descripcion', 'objetivo', 'lugar', 'cupo_maximo',
                'materiales', 'resultado_general', 'nivel_cumplimiento',
                'incidencias', 'recomendaciones', 'color',
                'created_by', 'updated_by',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('actividades_adulto', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
