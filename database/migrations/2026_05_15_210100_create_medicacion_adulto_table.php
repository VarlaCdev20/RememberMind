<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicacion_adulto', function (Blueprint $table) {
            $table->increments('cod_med_adulto');

            // FK → adulto_mayor
            $table->string('cod_am', 10);
            $table->foreign('cod_am')
                ->references('cod_am')->on('adulto_mayor')
                ->onUpdate('cascade')->onDelete('restrict');

            // Datos del medicamento
            $table->string('nombre_medicamento', 200);
            $table->string('dosis', 100);
            $table->string('frecuencia', 100);
            $table->string('via_administracion', 80)->nullable();
            $table->time('hora_programada')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('medico_indica', 200)->nullable();

            // FK → documentos_adulto_mayor (receta médica)
            $table->unsignedInteger('documento_receta')->nullable();
            $table->foreign('documento_receta')
                ->references('cod_doc_am')->on('documentos_adulto_mayor')
                ->onUpdate('cascade')->onDelete('set null');

            // Estado: ACTIVO, SUSPENDIDO, FINALIZADO, ARCHIVADO
            $table->string('estado', 30)->default('ACTIVO');
            $table->text('observacion')->nullable();

            // Registro
            $table->string('registrado_por', 20)->nullable();
            $table->foreign('registrado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('medicacion_adulto', function (Blueprint $table) {
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['documento_receta']);
            $table->dropForeign(['registrado_por']);
        });
        Schema::dropIfExists('medicacion_adulto');
    }
};
