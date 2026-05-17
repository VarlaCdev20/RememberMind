<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documentos_adulto_mayor', function (Blueprint $table) {
            $table->increments('cod_doc_am');

            $table->string('nom_doc', 120);
            $table->string('tipo_doc', 100);
            $table->string('ruta_archivo', 255);
            $table->string('extension', 100);
            $table->date('fecha_doc')->nullable();
            $table->text('observaciones')->nullable();

            $table->unsignedInteger('cod_am');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_adulto_mayor', function (Blueprint $table) {
            $table->dropForeign(['cod_am']);
        });

        Schema::dropIfExists('documentos_adulto_mayor');
    }
};