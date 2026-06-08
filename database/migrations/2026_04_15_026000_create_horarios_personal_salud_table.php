<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_personal_salud', function (Blueprint $table) {
            $table->string('cod_hor_per_sal', 20)->primary();
            $table->string('dia_semana', 20);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('turno', 50)->default('MAÑANA');
            $table->string('estado', 20)->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->string('cod_usu', 20);
            $table->timestamps();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_personal_salud');
    }
};