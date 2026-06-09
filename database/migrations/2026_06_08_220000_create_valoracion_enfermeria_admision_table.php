<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoracion_enfermeria_admision', function (Blueprint $table) {
            $table->string('cod_val_enf', 20)->primary();
            $table->string('cod_am', 20);
            $table->date('fecha_valoracion');
            $table->time('hora_valoracion')->nullable();
            $table->string('estado_general', 30)->nullable();
            $table->string('nivel_conciencia', 40)->nullable();
            $table->string('orientacion', 40)->nullable();
            $table->string('comunicacion', 40)->nullable();
            $table->boolean('hay_dolor')->default(false);
            $table->unsignedTinyInteger('intensidad_dolor')->nullable();
            $table->string('ubicacion_dolor', 120)->nullable();
            $table->string('movilidad', 40)->nullable();
            $table->string('apoyo_movilidad', 40)->nullable();
            $table->string('riesgo_caida', 20)->nullable();
            $table->string('piel_estado', 60)->nullable();
            $table->boolean('hay_heridas')->default(false);
            $table->text('ubicacion_heridas')->nullable();
            $table->string('higiene_ingreso', 60)->nullable();
            $table->string('continencia_basica', 60)->nullable();
            $table->string('alimentacion_aparente', 60)->nullable();
            $table->text('signos_vitales_iniciales')->nullable();
            $table->text('observacion')->nullable();
            $table->text('recomendacion_enfermeria')->nullable();
            $table->string('estado', 30)->default('COMPLETADA');
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'fecha_valoracion'], 'idx_val_enf_am_fecha');
            $table->index(['registrado_por', 'fecha_valoracion'], 'idx_val_enf_usuario_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoracion_enfermeria_admision');
    }
};
