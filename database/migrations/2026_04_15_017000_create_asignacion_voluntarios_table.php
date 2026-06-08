<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_voluntarios', function (Blueprint $table) {
            $table->string('cod_asig_vol', 20)->primary();
            $table->string('cod_vol', 20);
            $table->string('cod_am', 20);
            $table->date('fecha_asig');
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 50)->default('ACTIVA');
            $table->text('obser')->nullable();
            $table->timestamps();

            $table->foreign('cod_vol')
                ->references('cod_vol')
                ->on('voluntarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_voluntarios');
    }
};