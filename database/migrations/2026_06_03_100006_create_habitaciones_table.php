<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('habitaciones')) {
            return;
        }

        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id('cod_habitacion');

            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100)->nullable();
            $table->string('tipo_habitacion', 30);           // INDIVIDUAL, COMPARTIDA, UCI, OBSERVACION
            $table->string('ubicacion', 100)->nullable();    // Planta, ala, sección
            $table->unsignedSmallInteger('capacidad')->default(1);
            $table->string('estado', 20)->default('DISPONIBLE');
            // DISPONIBLE, OCUPADA, MANTENIMIENTO, BLOQUEADA
            $table->string('observacion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('tipo_habitacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
