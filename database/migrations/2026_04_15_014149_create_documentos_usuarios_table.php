<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_usuarios', function (Blueprint $table) {
            $table->increments('cod_doc_usu');

            $table->string('nom_doc', 120);
            $table->string('tipo_doc', 100);
            $table->string('ruta_archivo', 255);
            $table->string('extension', 100);
            $table->date('fecha_doc')->nullable();
            $table->text('observaciones')->nullable();

            $table->string('cod_usu', 20);

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('documentos_usuarios', function (Blueprint $table) {
            $table->dropForeign(['cod_usu']);
        });

        Schema::dropIfExists('documentos_usuarios');
    }
};