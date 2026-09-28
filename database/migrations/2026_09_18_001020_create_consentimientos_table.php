<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consentimientos', function (Blueprint $table) {
            $table->string('cod_consentimiento', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_admision', 20);
            $table->string('cod_residente_contacto', 20)->nullable();
            $table->string('cod_documento', 20)->nullable();
            $table->string('cod_usuario_registro', 20);
            $table->string('tipo_consentimiento', 80);
            $table->boolean('firma_residente');
            $table->dateTime('fecha_consentimiento');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_admision')->references('cod_admision')->on('admisiones')->restrictOnDelete();
            $table->foreign('cod_residente_contacto')->references('cod_residente_contacto')->on('residentes_contactos')->restrictOnDelete();
            $table->foreign('cod_documento')->references('cod_documento')->on('documentos')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimientos');
    }
};
