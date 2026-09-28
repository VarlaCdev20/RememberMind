<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('prescripciones', 'cod_med_adulto')) {
            return;
        }

        $registrosLegacy = DB::table('prescripciones')
            ->whereNotNull('cod_med_adulto')
            ->count();

        if ($registrosLegacy > 0) {
            throw new RuntimeException(
                "No se puede retirar prescripciones.cod_med_adulto: existen {$registrosLegacy} valores legacy sin depurar.",
            );
        }

        Schema::table('prescripciones', function (Blueprint $table) {
            $table->dropColumn('cod_med_adulto');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('prescripciones', 'cod_med_adulto')) {
            return;
        }

        Schema::table('prescripciones', function (Blueprint $table) {
            $table->string('cod_med_adulto', 30)->nullable();
        });
    }
};
