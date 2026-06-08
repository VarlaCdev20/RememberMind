<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_adulto_mayor', function (Blueprint $table) {
            $table->string('cod_doc_am', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('nombre', 150);
            $table->string('tipo_documento', 100);
            $table->string('ruta_archivo', 255);
            $table->date('fecha_subida');
            $table->string('estado', 50)->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->string('modulo_ref', 100)->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_adulto_mayor');
    }
};