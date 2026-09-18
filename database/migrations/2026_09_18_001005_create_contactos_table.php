<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->string('cod_contacto', 20)->primary();
            $table->string('cod_usuario', 20)->nullable()->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
