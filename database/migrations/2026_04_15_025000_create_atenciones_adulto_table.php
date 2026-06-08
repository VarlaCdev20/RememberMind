<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atenciones_adulto', function (Blueprint $table) {
            $table->string('cod_aten_adul', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_tipo_aten', 20);
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 50)->default('COMPLETADA');
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_tipo_aten')
                ->references('cod_tipo_aten')
                ->on('tipo_atenciones_adulto')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atenciones_adulto');
    }
};