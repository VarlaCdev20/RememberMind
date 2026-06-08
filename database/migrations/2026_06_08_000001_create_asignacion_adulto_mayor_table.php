<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_adulto_mayor', function (Blueprint $table) {
            $table->string('cod_asig_adulto', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_habitacion', 20)->nullable();
            $table->string('cod_cama', 20)->nullable();
            $table->date('fecha_asignacion');
            $table->time('hora_asignacion')->nullable();
            $table->string('estado', 50)->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_habitacion')
                ->references('cod_habitacion')
                ->on('habitaciones')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('cod_cama')
                ->references('cod_cama')
                ->on('camas')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_adulto_mayor');
    }
};
