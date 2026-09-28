<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->string('cod_turno', 20)->primary();
            $table->string('nombre', 50)->unique();
            $table->time('hora_inicio');
            $table->time('hora_cierre');
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
