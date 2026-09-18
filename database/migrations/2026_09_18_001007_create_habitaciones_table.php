<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->string('cod_habitacion', 20)->primary();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 80)->nullable();
            $table->string('tipo', 40)->nullable();
            $table->string('piso', 30)->nullable();
            $table->smallInteger('capacidad');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
