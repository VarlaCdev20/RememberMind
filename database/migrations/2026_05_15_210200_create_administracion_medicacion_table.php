<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administracion_medicacion', function (Blueprint $table) {
            $table->string('cod_admin_med', 20)->primary();
            $table->string('cod_med_adulto', 20);
            $table->string('cod_am', 20);
            $table->date('fecha');
            $table->time('hora_programada');
            $table->time('hora_real')->nullable();
            $table->boolean('administrado')->default(false);
            $table->text('motivo_omision')->nullable();
            $table->text('efecto_observado')->nullable();
            $table->text('observacion')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();

            $table->index(['cod_med_adulto', 'fecha'], 'idx_admin_med_fecha');
            $table->index(['cod_am', 'fecha'], 'idx_admin_am_fecha');

            $table->foreign('cod_med_adulto')
                ->references('cod_med_adulto')->on('medicacion_adulto')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administracion_medicacion');
    }
};
