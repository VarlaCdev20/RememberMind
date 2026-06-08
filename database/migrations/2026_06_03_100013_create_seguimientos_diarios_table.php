<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seguimientos_diarios')) {
            return;
        }

        Schema::create('seguimientos_diarios', function (Blueprint $table) {
            $table->string('cod_seg_diario', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_turno', 20);
            $table->string('cod_plan', 20)->nullable();
            $table->string('registrado_por', 20)->nullable();

            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();

            // Estado general
            $table->string('estado_general', 30)->nullable();

            // Alimentación e hidratación
            $table->string('alimentacion', 20)->nullable();
            $table->unsignedTinyInteger('porcentaje_alimentacion')->nullable(); // 0-100
            $table->string('hidratacion', 20)->nullable();

            // Movilidad
            $table->string('movilidad', 30)->nullable();
            $table->boolean('intento_caminar_solo')->default(false);

            // Higiene
            $table->string('higiene', 20)->nullable();

            // Sueño
            $table->string('sueno', 30)->nullable();

            // Estado cognitivo
            $table->string('orientacion', 30)->nullable();
            $table->boolean('repite_preguntas')->default(false);
            $table->boolean('confusion_observable')->default(false);

            // Conducta y participación
            $table->string('conducta', 30)->nullable();
            $table->string('participacion', 30)->nullable();

            // Alertas
            $table->boolean('incidente')->default(false);
            $table->boolean('requiere_medico')->default(false);
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_turno')
                ->references('cod_turno')
                ->on('turnos_enfermeria')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_plan')
                ->references('cod_plan')
                ->on('planes_cuidado')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // Evitar duplicados: mismo adulto, fecha, turno
            $table->unique(['cod_am', 'fecha', 'cod_turno'], 'uq_seguimiento_adulto_fecha_turno');
            $table->index(['cod_am', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos_diarios');
    }
};
