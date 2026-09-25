<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_estudio', function (Blueprint $table) {
            $table->string('cod_resultado_estudio', 20)->primary();
            $table->string('cod_estudio', 20);
            $table->string('cod_componente', 20);
            $table->decimal('valor_numerico', 18, 6)->nullable();
            $table->text('valor_texto')->nullable();
            $table->string('unidad', 40)->nullable();
            $table->string('rango_referencia', 120)->nullable();
            $table->string('clasificacion', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->unique(['cod_estudio', 'cod_componente']);
            $table->foreign('cod_estudio')->references('cod_estudio')->on('estudios_clinicos')->restrictOnDelete();
            $table->foreign('cod_componente')->references('cod_componente')->on('componentes_estudio')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_estudio');
    }
};
