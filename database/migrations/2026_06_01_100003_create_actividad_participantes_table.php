<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('actividad_participantes')) {
            return;
        }

        Schema::create('actividad_participantes', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('cod_act_adul');
            $table->string('cod_am', 10);
            // varchar(10) — mismo tipo que adulto_mayor.cod_am y actividades_adulto.cod_am en la DB real

            $table->string('estado_asistencia', 20)->default('INSCRITO');
            // INSCRITO | ASISTIO | FALTO | JUSTIFICADO

            $table->string('nivel_participacion', 20)->nullable()->default('NO_APLICA');
            // ALTA | MEDIA | BAJA | NO_APLICA

            $table->string('estado_observado', 30)->nullable();
            // ACTIVO | TRANQUILO | AISLADO | IRRITABLE | CANSADO | COLABORADOR | DESORIENTADO

            $table->text('observacion_individual')->nullable();

            $table->boolean('requiere_seguimiento')->default(false);

            $table->string('registrado_por', 20)->nullable();
            // FK soft hacia users.cod_usu

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_act_adul')
                ->references('cod_act_adul')
                ->on('actividades_adulto')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Un adulto no puede estar inscrito dos veces en la misma actividad
            $table->unique(['cod_act_adul', 'cod_am'], 'uq_participante_actividad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_participantes');
    }
};
