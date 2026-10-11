<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objetivos_signos_vitales', function (Blueprint $table): void {
            $table->string('cod_objetivo_signo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('parametro', 30);
            $table->decimal('min_objetivo', 8, 2)->nullable();
            $table->decimal('max_objetivo', 8, 2)->nullable();
            $table->decimal('min_critico', 8, 2)->nullable();
            $table->decimal('max_critico', 8, 2)->nullable();
            $table->dateTime('vigente_desde', 6);
            $table->dateTime('vigente_hasta', 6)->nullable();
            $table->string('estado', 20);
            $table->string('motivo', 1000);

            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->index('cod_personal');
            $table->index(['cod_residente', 'parametro', 'estado']);
            $table->unique(['cod_residente', 'parametro', 'vigente_desde'], 'uq_objetivo_signo_version');
        });
        if (in_array(DB::getDriverName(), ['pgsql', 'sqlite'], true)) {
            DB::statement("CREATE UNIQUE INDEX uq_objetivo_signo_vigente ON objetivos_signos_vitales (cod_residente, parametro) WHERE estado = 'VIGENTE'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('objetivos_signos_vitales');
    }
};
