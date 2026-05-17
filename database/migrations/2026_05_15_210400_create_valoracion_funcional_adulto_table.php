<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoracion_funcional_adulto', function (Blueprint $table) {
            $table->increments('cod_val_func');

            // FK → adulto_mayor
            $table->string('cod_am', 10);
            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->date('fecha_valoracion');

            // Autonomía básica (Actividades de la Vida Diaria)
            $table->boolean('come_solo')->default(false);
            $table->boolean('se_bana_solo')->default(false);
            $table->boolean('se_viste_solo')->default(false);
            $table->boolean('va_bano_solo')->default(false);
            $table->boolean('camina_solo')->default(false);

            // Uso de dispositivos de asistencia
            $table->boolean('usa_baston')->default(false);
            $table->boolean('usa_andador')->default(false);
            $table->boolean('usa_silla_ruedas')->default(false);

            // Capacidades sensoriales y conductuales
            $table->boolean('baja_vision')->default(false);
            $table->boolean('baja_audicion')->default(false);
            $table->boolean('dificultad_hablar')->default(false);
            $table->boolean('molestia_luz')->default(false);
            $table->boolean('molestia_ruido')->default(false);
            $table->boolean('se_asusta_facil')->default(false);
            $table->boolean('necesita_supervision')->default(false);

            // Clasificación funcional
            // INDEPENDIENTE, DEPENDENCIA_PARCIAL, ALTA_DEPENDENCIA, SUPERVISION_PERMANENTE
            $table->string('nivel_dependencia', 50);

            $table->text('observacion')->nullable();

            // Registro
            $table->string('registrado_por', 20)->nullable();
            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();

            // Índice para historial por adulto
            $table->index(['cod_am', 'fecha_valoracion'], 'idx_valfunc_am_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('valoracion_funcional_adulto', function (Blueprint $table) {
            $table->dropIndex('idx_valfunc_am_fecha');
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['registrado_por']);
        });
        Schema::dropIfExists('valoracion_funcional_adulto');
    }
};
