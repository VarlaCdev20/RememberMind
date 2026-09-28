<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->string('cod_usuario', 20)->primary();
            $table->string('correo', 120)->unique();
            $table->string('contrasena', 255);
            $table->string('foto', 255)->nullable();
            $table->string('estado', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
