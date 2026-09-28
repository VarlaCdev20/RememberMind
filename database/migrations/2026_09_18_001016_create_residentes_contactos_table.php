<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residentes_contactos', function (Blueprint $table) {
            $table->string('cod_residente_contacto', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_contacto', 20);
            $table->string('parentesco', 40);
            $table->boolean('responsable_principal');
            $table->boolean('contacto_emergencia');
            $table->boolean('autoriza_informacion');
            $table->boolean('autoriza_salida');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'cod_contacto', 'estado']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residentes_contactos');
    }
};
