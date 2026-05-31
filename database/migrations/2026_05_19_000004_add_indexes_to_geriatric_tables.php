<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluaciones_geriatricas', function (Blueprint $table) {
            $table->index('cod_am');
            $table->index('cod_instrumento');
            $table->index('fecha_eval');
            $table->index('estado_eval');
            $table->index('nivel_alerta');
        });

        Schema::table('instrumentos_geriatricos', function (Blueprint $table) {
            $table->index('cod_area');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('evaluaciones_geriatricas', function (Blueprint $table) {
            $table->dropIndex(['cod_am']);
            $table->dropIndex(['cod_instrumento']);
            $table->dropIndex(['fecha_eval']);
            $table->dropIndex(['estado_eval']);
            $table->dropIndex(['nivel_alerta']);
        });

        Schema::table('instrumentos_geriatricos', function (Blueprint $table) {
            $table->dropIndex(['cod_area']);
            $table->dropIndex(['estado']);
        });
    }
};
