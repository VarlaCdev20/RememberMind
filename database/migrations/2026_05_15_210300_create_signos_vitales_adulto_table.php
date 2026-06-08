<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signos_vitales_adulto', function (Blueprint $table) {
            $table->string('cod_signo', 20)->primary();
            $table->string('cod_am', 20);
            $table->date('fecha');
            $table->time('hora');
            $table->string('presion_arterial', 20)->nullable();
            $table->integer('presion_sistolica')->nullable();
            $table->integer('presion_diastolica')->nullable();
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->integer('saturacion')->nullable();
            $table->decimal('glucosa', 6, 2)->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('talla', 5, 2)->nullable();
            $table->decimal('imc', 5, 2)->nullable();
            $table->string('dolor', 50)->nullable();
            $table->text('observacion')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->string('estado', 50)->default('VIGENTE');
            $table->text('motivo_anulacion')->nullable();
            $table->string('anulado_por', 20)->nullable();
            $table->dateTime('fecha_anulacion')->nullable();
            $table->timestamps();

            $table->index(['cod_am', 'fecha'], 'idx_signos_am_fecha');

            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('anulado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signos_vitales_adulto');
    }
};
