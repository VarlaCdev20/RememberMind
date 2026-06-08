<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidad_voluntarios', function (Blueprint $table) {
            $table->string('cod_hor_vol', 20)->primary();
            $table->string('cod_vol', 20);
            $table->string('dia_semana', 20);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('estado', 50)->default('ACTIVO');
            $table->text('obser')->nullable();
            $table->timestamps();

            $table->foreign('cod_vol')
                ->references('cod_vol')
                ->on('voluntarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_voluntarios');
    }
};