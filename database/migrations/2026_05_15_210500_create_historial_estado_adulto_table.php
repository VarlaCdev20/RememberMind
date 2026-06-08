<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estado_adulto', function (Blueprint $table) {
            $table->string('cod_hist_estado', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->timestamp('fecha_cambio');
            $table->text('motivo');
            $table->string('documento_respaldo', 20)->nullable();
            $table->string('cambiado_por', 20)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('estado_anterior')
                ->references('cod_est_adul')->on('estado_adulto')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('estado_nuevo')
                ->references('cod_est_adul')->on('estado_adulto')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('documento_respaldo')
                ->references('cod_doc_am')->on('documentos_adulto_mayor')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('cambiado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            // Índice para consultas de historial por adulto
            $table->index(['cod_am', 'fecha_cambio'], 'idx_histest_am_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estado_adulto');
    }
};
