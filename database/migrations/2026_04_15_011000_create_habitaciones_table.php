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
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->string('tipo_habitacion', 50)->default('INDIVIDUAL');
            $table->integer('capacidad')->default(1);
            $table->string('estado', 30)->default('DISPONIBLE');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
