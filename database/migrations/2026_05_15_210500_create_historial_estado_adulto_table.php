<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estado_adulto', function (Blueprint $table) {
            $table->increments('cod_hist_estado');

            // FK → adulto_mayor
            $table->string('cod_am', 10);
            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('restrict');

            // FK → estado_adulto (estado anterior, puede ser null si es primer registro)
            $table->unsignedInteger('estado_anterior')->nullable();
            $table->foreign('estado_anterior')
                ->references('cod_est_adul')->on('estado_adulto')
                ->onUpdate('cascade')->onDelete('restrict');

            // FK → estado_adulto (estado nuevo)
            $table->unsignedInteger('estado_nuevo');
            $table->foreign('estado_nuevo')
                ->references('cod_est_adul')->on('estado_adulto')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->timestamp('fecha_cambio');
            $table->text('motivo');

            // FK → documentos_adulto_mayor (respaldo documental)
            $table->unsignedInteger('documento_respaldo')->nullable();
            $table->foreign('documento_respaldo')
                ->references('cod_doc_am')->on('documentos_adulto_mayor')
                ->onUpdate('cascade')->onDelete('set null');

            // FK → users (quién realizó el cambio)
            $table->string('cambiado_por', 20)->nullable();
            $table->foreign('cambiado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->text('observacion')->nullable();

            $table->timestamps();

            // Índice para consultas de historial por adulto
            $table->index(['cod_am', 'fecha_cambio'], 'idx_histest_am_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('historial_estado_adulto', function (Blueprint $table) {
            $table->dropIndex('idx_histest_am_fecha');
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['estado_anterior']);
            $table->dropForeign(['estado_nuevo']);
            $table->dropForeign(['documento_respaldo']);
            $table->dropForeign(['cambiado_por']);
        });
        Schema::dropIfExists('historial_estado_adulto');
    }
};
