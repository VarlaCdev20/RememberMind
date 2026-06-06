<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('camas')) {
            return;
        }

        Schema::create('camas', function (Blueprint $table) {
            $table->id('cod_cama');

            $table->unsignedBigInteger('cod_habitacion');
            $table->string('codigo', 20);                    // B1, B2, etc.
            $table->string('estado', 20)->default('DISPONIBLE');
            // DISPONIBLE, OCUPADA, MANTENIMIENTO, BLOQUEADA
            $table->string('observacion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_habitacion')
                ->references('cod_habitacion')
                ->on('habitaciones')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unique(['cod_habitacion', 'codigo'], 'uq_cama_habitacion_codigo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camas');
    }
};
