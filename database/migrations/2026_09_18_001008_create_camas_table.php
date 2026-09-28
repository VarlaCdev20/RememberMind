<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camas', function (Blueprint $table) {
            $table->string('cod_cama', 20)->primary();
            $table->string('cod_habitacion', 20);
            $table->string('codigo', 30)->unique();
            $table->string('tipo', 40)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_habitacion')->references('cod_habitacion')->on('habitaciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camas');
    }
};
