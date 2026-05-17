<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_cognitivas', function (Blueprint $table) {
            $table->string('cod_eval_cog', 12)->primary();

            $table->string('cod_am', 10);
            $table->unsignedBigInteger('cod_tipo_eval');
            $table->integer('cod_per_sal');

            $table->date('fecha_eval');
            $table->time('hora_eval')->nullable();

            $table->decimal('puntaje_total', 5, 2)->nullable();
            $table->decimal('puntaje_maximo', 5, 2)->nullable();

            $table->string('resultado_interpretacion', 120)->nullable();
            $table->string('nivel_riesgo', 30)->nullable();

            $table->text('observaciones')->nullable();

            $table->string('estado_eval', 30)->default('BORRADOR');

            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('cod_tipo_eval')
                ->references('cod_tipo_eval')
                ->on('tipo_evaluacion_cognitiva')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('cod_per_sal')
                ->references('cod_per_sal')
                ->on('personal_salud')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('cod_am');
            $table->index('cod_tipo_eval');
            $table->index('cod_per_sal');
            $table->index('fecha_eval');
            $table->index('nivel_riesgo');
            $table->index('estado_eval');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_cognitivas');
    }
};