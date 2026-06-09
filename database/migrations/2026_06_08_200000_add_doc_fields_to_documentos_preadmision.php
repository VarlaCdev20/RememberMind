<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_preadmision', function (Blueprint $table) {
            if (! Schema::hasColumn('documentos_preadmision', 'grupo_documento')) {
                $table->string('grupo_documento', 40)->default('solicitante')->after('nombre_documento');
            }
            if (! Schema::hasColumn('documentos_preadmision', 'es_generado_sistema')) {
                $table->boolean('es_generado_sistema')->default(false)->after('es_institucional');
            }
            if (! Schema::hasColumn('documentos_preadmision', 'bloquea_avance')) {
                $table->boolean('bloquea_avance')->default(false)->after('obligatorio');
            }
            if (! Schema::hasColumn('documentos_preadmision', 'permite_48h')) {
                $table->boolean('permite_48h')->default(false)->after('bloquea_avance');
            }
            if (! Schema::hasColumn('documentos_preadmision', 'fecha_limite_entrega')) {
                $table->timestamp('fecha_limite_entrega')->nullable()->after('estado');
            }
            if (! Schema::hasColumn('documentos_preadmision', 'fecha_generacion')) {
                $table->timestamp('fecha_generacion')->nullable()->after('fecha_limite_entrega');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_preadmision', function (Blueprint $table) {
            $table->dropColumn([
                'grupo_documento',
                'es_generado_sistema',
                'bloquea_avance',
                'permite_48h',
                'fecha_limite_entrega',
                'fecha_generacion',
            ]);
        });
    }
};
