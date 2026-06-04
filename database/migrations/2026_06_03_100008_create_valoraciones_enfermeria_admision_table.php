<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('valoraciones_enfermeria_admision')) {
            return;
        }

        Schema::create('valoraciones_enfermeria_admision', function (Blueprint $table) {
            $table->id('cod_val_enf');

            $table->string('cod_am', 10);
            $table->date('fecha');
            $table->time('hora');

            // Estado general
            $table->string('estado_general', 30)->nullable();
            // BUENO, REGULAR, MALO, CRITICO
            $table->string('nivel_conciencia', 30)->nullable();
            // ALERTA, SOMNOLIENTO, ESTUPOROSO, COMATOSO
            $table->string('orientacion', 30)->nullable();
            // ORIENTADO, DESORIENTADO_TIEMPO, DESORIENTADO_ESPACIO, DESORIENTADO_PERSONA, DESORIENTADO_TOTAL
            $table->string('comunicacion', 30)->nullable();
            // FLUIDA, LIMITADA, NULA

            // Dolor
            $table->boolean('dolor_actual')->default(false);
            $table->unsignedTinyInteger('intensidad_dolor')->nullable();  // 0-10
            $table->string('ubicacion_dolor', 100)->nullable();

            // Movilidad
            $table->string('movilidad', 30)->nullable();
            // INDEPENDIENTE, ASISTIDA, DEPENDIENTE, EN_CAMA
            $table->string('usa_apoyo_movilidad', 100)->nullable();       // andador, silla, bastón

            // Seguridad
            $table->string('riesgo_caida', 20)->nullable();
            // BAJO, MEDIO, ALTO

            // Piel e higiene
            $table->string('piel_estado', 30)->nullable();
            // INTEGRA, CON_LESIONES, ERITEMA, ULCERA
            $table->boolean('presenta_heridas')->default(false);
            $table->string('ubicacion_heridas', 200)->nullable();
            $table->string('higiene_ingreso', 20)->nullable();
            // ADECUADA, REGULAR, INADECUADA

            // Decisiones
            $table->boolean('requiere_atencion_inmediata')->default(false);
            $table->boolean('puede_pasar_valoracion_medica')->default(false);
            $table->text('recomendacion_enfermeria')->nullable();
            $table->text('observacion')->nullable();

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

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'fecha']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones_enfermeria_admision');
    }
};
