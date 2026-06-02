<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_actividades_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('tipo_actividades_adulto', 'categoria')) {
                $table->string('categoria', 30)->nullable()->after('descripcion');
                // Valores: COGNITIVA, FISICA, SOCIAL, EMOCIONAL, ESPIRITUAL, EDUCATIVA
            }
            if (! Schema::hasColumn('tipo_actividades_adulto', 'duracion_estimada_minutos')) {
                $table->unsignedSmallInteger('duracion_estimada_minutos')->nullable()->after('categoria');
            }
            if (! Schema::hasColumn('tipo_actividades_adulto', 'requiere_profesional')) {
                $table->boolean('requiere_profesional')->default(false)->after('duracion_estimada_minutos');
            }
            if (! Schema::hasColumn('tipo_actividades_adulto', 'activo')) {
                $table->boolean('activo')->default(true)->after('requiere_profesional');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_actividades_adulto', function (Blueprint $table) {
            $columns = ['categoria', 'duracion_estimada_minutos', 'requiere_profesional', 'activo'];

            foreach ($columns as $col) {
                if (Schema::hasColumn('tipo_actividades_adulto', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
