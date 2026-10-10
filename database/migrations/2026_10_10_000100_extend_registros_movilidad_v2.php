<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_movilidad', function (Blueprint $table): void {
            $table->string('motivo_registro', 30)->nullable();
            $table->string('actividad_realizada', 40)->nullable();
            $table->decimal('distancia_metros', 7, 2)->nullable();
            $table->boolean('dolor_movilidad')->nullable();
            $table->boolean('mareo')->nullable();
            $table->boolean('disnea')->nullable();
            $table->boolean('debilidad')->nullable();
            $table->string('cambio_habitual', 20)->nullable();
            $table->string('tolerancia_movilidad', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registros_movilidad', function (Blueprint $table): void {
            $table->dropColumn(['motivo_registro', 'actividad_realizada', 'distancia_metros',
                'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual', 'tolerancia_movilidad']);
        });
    }
};
