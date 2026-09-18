<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguros_residente', function (Blueprint $table) {
            $table->string('cod_seguro', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('entidad', 120);
            $table->string('plan', 100)->nullable();
            $table->string('numero_afiliacion', 80)->nullable();
            $table->string('titular', 160)->nullable();
            $table->text('cobertura')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguros_residente');
    }
};
