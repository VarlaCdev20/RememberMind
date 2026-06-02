<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('actividades_adulto', 'evaluacion_final')) {
                $table->text('evaluacion_final')->nullable()->after('resultado_general');
            }
        });
    }

    public function down(): void
    {
        Schema::table('actividades_adulto', function (Blueprint $table) {
            if (Schema::hasColumn('actividades_adulto', 'evaluacion_final')) {
                $table->dropColumn('evaluacion_final');
            }
        });
    }
};
