<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumentos', function (Blueprint $table) {
            $table->string('cod_instrumento', 20)->primary();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 160);
            $table->string('tipo', 50);
            $table->string('version', 30)->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->string('estado', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumentos');
    }
};
