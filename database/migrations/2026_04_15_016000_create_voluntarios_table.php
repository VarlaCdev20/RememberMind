<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntarios', function (Blueprint $table) {
            $table->string('cod_vol', 20)->primary();
            $table->string('nombres', 100);
            $table->string('ap_paterno', 80);
            $table->string('ap_materno', 80)->nullable();
            $table->string('ci', 20)->nullable()->unique();
            $table->string('celular', 20)->nullable();
            $table->string('correo', 120)->nullable();
            $table->date('fecha_nac')->nullable();
            $table->string('profesion_ocupacion', 150)->nullable();
            $table->string('estado', 50)->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->string('cod_usu', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntarios');
    }
};