<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_adulto_mayor', function (Blueprint $table) {
            if (! Schema::hasColumn('documentos_adulto_mayor', 'origen_modulo')) {
                $table->string('origen_modulo', 50)->nullable()->after('cod_am');
                // ADMISION, SALUD, VALORACION, PLAN, SEGUIMIENTO, OTRO
            }
            if (! Schema::hasColumn('documentos_adulto_mayor', 'referencia_id')) {
                $table->unsignedBigInteger('referencia_id')->nullable()->after('origen_modulo');
                // ID del registro al que pertenece el documento
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_adulto_mayor', function (Blueprint $table) {
            $table->dropColumnIfExists('referencia_id');
            $table->dropColumnIfExists('origen_modulo');
        });
    }
};
