<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preadmisiones', function (Blueprint $table) {
            $table->string('cod_preadmision', 20)->primary();
            $table->string('cod_contacto', 20)->nullable();
            $table->string('cod_usuario_registro', 20);
            $table->string('cod_usuario_revision', 20)->nullable();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->nullable();
            $table->string('expedicion_documento', 20)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('genero', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->text('motivo_ingreso');
            $table->string('procedencia', 120)->nullable();
            $table->string('tipo_ingreso', 50)->nullable();
            $table->string('permanencia', 50)->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->text('descripcion_caso')->nullable();
            $table->dateTime('fecha_solicitud');
            $table->dateTime('fecha_revision')->nullable();
            $table->string('estado', 20);
            $table->text('motivo_rechazo')->nullable();
            $table->index(['numero_documento', 'estado']);
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_usuario_revision')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preadmisiones');
    }
};
