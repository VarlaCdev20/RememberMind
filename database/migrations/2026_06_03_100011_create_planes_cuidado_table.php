<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('planes_cuidado')) {
            return;
        }

        Schema::create('planes_cuidado', function (Blueprint $table) {
            $table->id('cod_plan');

            $table->string('cod_am', 10);
            $table->string('tipo_plan', 20)->default('INICIAL');
            // INICIAL, AJUSTE, REEVALUACION
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('nivel_cuidado', 30)->default('ESTANDAR');
            // PREVENTIVO, ESTANDAR, INTENSIVO, PALIATIVO
            $table->string('estado', 20)->default('BORRADOR');
            // BORRADOR, ACTIVO, REEMPLAZADO, CERRADO, ANULADO
            $table->string('origen', 50)->nullable();
            // ADMISION, REEVALUACION_PERIODICA, CAMBIO_CONDICION, SOLICITUD_MEDICA
            $table->text('resumen')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('creado_por', 20)->nullable();    // FK users
            $table->string('validado_por', 20)->nullable();  // FK users

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('creado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('validado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'estado']);
            $table->index(['cod_am', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_cuidado');
    }
};
