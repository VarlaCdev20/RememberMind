<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_geriatricas', function (Blueprint $table) {
            $table->string('cod_eval_ger', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_instrumento', 20);
            $table->date('fecha_eval');
            $table->decimal('puntaje', 8, 2)->nullable();
            $table->string('resultado_cualitativo', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('evaluador_id', 20)->nullable();
            $table->string('estado', 30)->default('COMPLETADA');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_instrumento')
                ->references('cod_instrumento')
                ->on('instrumentos_geriatricos')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('evaluador_id')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_geriatricas');
    }
};
