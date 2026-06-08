<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos_institucionales', function (Blueprint $table) {
            $table->string('cod_turno', 20)->primary();
            $table->string('nombre', 100);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->text('descripcion')->nullable();
            $table->string('color', 30)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('creado_por', 20)->nullable();
            $table->string('actualizado_por', 20)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('creado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('actualizado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_institucionales');
    }
};
