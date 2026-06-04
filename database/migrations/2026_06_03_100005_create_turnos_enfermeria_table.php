<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnos_enfermeria')) {
            return;
        }

        Schema::create('turnos_enfermeria', function (Blueprint $table) {
            $table->increments('cod_turno');

            $table->string('nombre', 50)->unique();          // MAÑANA, TARDE, NOCHE, MADRUGADA
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedTinyInteger('orden')->unique();  // 1, 2, 3, 4
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, INACTIVO
            $table->string('observacion')->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_enfermeria');
    }
};
