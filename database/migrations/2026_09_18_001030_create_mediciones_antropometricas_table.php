<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediciones_antropometricas', function (Blueprint $table) {
            $table->string('cod_medicion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->dateTime('fecha_hora');
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('talla', 5, 2)->nullable();
            $table->decimal('imc', 5, 2)->nullable();
            $table->decimal('perimetro_braquial', 6, 2)->nullable();
            $table->decimal('perimetro_pantorrilla', 6, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediciones_antropometricas');
    }
};
