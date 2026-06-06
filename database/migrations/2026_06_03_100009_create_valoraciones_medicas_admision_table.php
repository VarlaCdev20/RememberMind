<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('valoraciones_medicas_admision')) {
            return;
        }

        Schema::create('valoraciones_medicas_admision', function (Blueprint $table) {
            $table->id('cod_val_med');

            $table->string('cod_am', 10);
            $table->unsignedBigInteger('cod_val_enf')->nullable();        // valoración enfermería base
            $table->date('fecha');
            $table->time('hora');

            // Antecedentes y diagnóstico
            $table->text('diagnosticos_referidos')->nullable();
            $table->text('antecedentes_relevantes')->nullable();
            $table->text('medicacion_actual_resumen')->nullable();
            $table->text('alergias_referidas')->nullable();

            // Condición médica
            $table->string('condicion_medica_general', 30)->nullable();
            // ESTABLE, COMPENSADO, DESCOMPENSADO, CRITICO
            $table->string('estado_neurologico_basico', 100)->nullable();
            $table->string('nivel_dependencia_sugerido', 30)->nullable();
            // INDEPENDIENTE, PARCIALMENTE_DEPENDIENTE, TOTALMENTE_DEPENDIENTE

            // Decisión de admisión
            $table->string('resultado_admision', 20);
            // ADMITIDO, NO_ADMITIDO, DERIVADO, OBSERVADO, CANCELADO
            $table->text('motivo_decision');
            $table->text('recomendacion_medica')->nullable();
            $table->boolean('requiere_seguimiento_especial')->default(false);

            // Trazabilidad
            $table->string('registrado_por', 20)->nullable();
            $table->string('estado', 20)->default('BORRADOR');
            // BORRADOR, COMPLETADA, REVISADA, ANULADA

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_val_enf')
                ->references('cod_val_enf')
                ->on('valoraciones_enfermeria_admision')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'fecha']);
            $table->index('resultado_admision');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones_medicas_admision');
    }
};
