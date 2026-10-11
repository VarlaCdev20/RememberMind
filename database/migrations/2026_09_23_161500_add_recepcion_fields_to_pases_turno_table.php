<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pases_turno', function (Blueprint $table) {
            $table->dateTime('fecha_hora_recepcion')->nullable()->after('estado');
            $table->text('observacion_recepcion')->nullable()->after('fecha_hora_recepcion');
        });
    }

    public function down(): void
    {
        Schema::table('pases_turno', function (Blueprint $table) {
            $table->dropColumn(['fecha_hora_recepcion', 'observacion_recepcion']);
        });
    }
};
