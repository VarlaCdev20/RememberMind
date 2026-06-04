<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_usuarios', function (Blueprint $table) {
            // Control de archivo
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('tamanio')->nullable();          // bytes

            // Estado documental: PENDIENTE, CARGADO, OBSERVADO, APROBADO, VENCIDO
            $table->string('estado', 25)->default('PENDIENTE');

            // Trazabilidad
            $table->integer('subido_por')->nullable();
            $table->integer('validado_por')->nullable();
            $table->timestamp('fecha_validacion')->nullable();
            $table->text('observacion_validacion')->nullable();

            // Plazo de regularización para documentos pendientes
            $table->date('fecha_vencimiento_plazo')->nullable();

            $table->foreign('subido_por')
                ->references('cod_usu')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('validado_por')
                ->references('cod_usu')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_usuarios', function (Blueprint $table) {
            $table->dropForeign(['subido_por']);
            $table->dropForeign(['validado_por']);
            $table->dropColumn([
                'mime_type',
                'tamanio',
                'estado',
                'subido_por',
                'validado_por',
                'fecha_validacion',
                'observacion_validacion',
                'fecha_vencimiento_plazo',
            ]);
        });
    }
};
