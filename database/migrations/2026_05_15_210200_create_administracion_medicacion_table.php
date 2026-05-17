<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administracion_medicacion', function (Blueprint $table) {
            $table->increments('cod_admin_med');

            // FK → medicacion_adulto
            $table->unsignedInteger('cod_med_adulto');
            $table->foreign('cod_med_adulto')
                ->references('cod_med_adulto')->on('medicacion_adulto')
                ->onUpdate('cascade')->onDelete('restrict');

            // FK → adulto_mayor (redundante para consultas rápidas)
            $table->string('cod_am', 10);
            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('restrict');

            // Registro de administración
            $table->date('fecha');
            $table->time('hora_programada');
            $table->time('hora_real')->nullable();
            $table->boolean('administrado')->default(false);
            $table->text('motivo_omision')->nullable();
            $table->text('efecto_observado')->nullable();
            $table->text('observacion')->nullable();

            // Registro
            $table->string('registrado_por', 20)->nullable();
            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();

            // Índice compuesto para consultas frecuentes: qué medicamento, qué día
            $table->index(['cod_med_adulto', 'fecha'], 'idx_admin_med_fecha');
            $table->index(['cod_am', 'fecha'], 'idx_admin_am_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('administracion_medicacion', function (Blueprint $table) {
            $table->dropIndex('idx_admin_med_fecha');
            $table->dropIndex('idx_admin_am_fecha');
            $table->dropForeign(['cod_med_adulto']);
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['registrado_por']);
        });
        Schema::dropIfExists('administracion_medicacion');
    }
};
