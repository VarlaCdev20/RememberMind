<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->string('cod_documento', 20);
            $table->string('cod_preadmision', 20)->nullable();
            $table->string('cod_residente', 20)->nullable();
            $table->string('cod_usuario', 20)->nullable();
            $table->string('cod_contacto', 20)->nullable();
            $table->string('cod_documento_anterior', 20)->nullable();
            $table->string('cod_usuario_validacion', 20)->nullable();
            $table->string('tipo_documento', 60);
            $table->string('nombre', 160);
            $table->string('ruta_archivo', 500);
            $table->string('tipo_archivo', 80);
            $table->string('hash_archivo', 128);
            $table->date('fecha_vencimiento')->nullable();
            $table->dateTime('fecha_validacion')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->primary('cod_documento');
            $table->foreign('cod_preadmision')->references('cod_preadmision')->on('preadmisiones')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
            $table->foreign('cod_usuario_validacion')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->foreign('cod_documento_anterior')->references('cod_documento')->on('documentos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
