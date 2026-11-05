<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Tres columnas opcionales aprobadas expresamente el 04/11/2026. Sin backfill. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->string('pais_documento', 2)->nullable();
            $table->string('tipo_documento', 30)->nullable();
            $table->string('pais_telefono', 2)->nullable();
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL lock_timeout = '10s'");
                DB::statement('LOCK TABLE personal IN ACCESS EXCLUSIVE MODE');
            } else {
                DB::statement('UPDATE personal SET pais_documento=pais_documento WHERE 1=0');
            }
            if (DB::table('personal')->whereNotNull('pais_documento')->orWhereNotNull('tipo_documento')->orWhereNotNull('pais_telefono')->exists()) {
                throw new LogicException('Los países y tipos de documento registrados no se eliminan por rollback.');
            }
            Schema::table('personal', fn (Blueprint $table) => $table->dropColumn(['pais_documento', 'tipo_documento', 'pais_telefono']));
        });
    }
};
