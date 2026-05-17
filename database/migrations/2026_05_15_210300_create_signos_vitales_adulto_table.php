<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signos_vitales_adulto', function (Blueprint $table) {
            $table->increments('cod_signo');

            // FK → adulto_mayor
            $table->string('cod_am', 10);
            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('restrict');

            // Control temporal
            $table->date('fecha');
            $table->time('hora');

            // Signos vitales
            $table->string('presion_arterial', 20)->nullable();       // ej: "120/80"
            $table->integer('frecuencia_cardiaca')->nullable();       // bpm
            $table->decimal('temperatura', 4, 1)->nullable();         // ej: 36.5
            $table->integer('saturacion')->nullable();                // % SpO2
            $table->decimal('glucosa', 6, 2)->nullable();             // mg/dL
            $table->decimal('peso', 5, 2)->nullable();                // kg
            $table->decimal('talla', 5, 2)->nullable();               // cm
            $table->decimal('imc', 5, 2)->nullable();                 // calculado
            $table->string('dolor', 50)->nullable();                  // escala o descripción

            $table->text('observacion')->nullable();

            // Registro
            $table->string('registrado_por', 20)->nullable();
            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();

            // Índice para consultas de historial por adulto y fecha
            $table->index(['cod_am', 'fecha'], 'idx_signos_am_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            $table->dropIndex('idx_signos_am_fecha');
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['registrado_por']);
        });
        Schema::dropIfExists('signos_vitales_adulto');
    }
};
