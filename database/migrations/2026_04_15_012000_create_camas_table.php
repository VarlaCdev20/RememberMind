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
            $table->string('codigo', 50)->unique();
            $table->string('estado', 30)->default('DISPONIBLE');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('cod_habitacion')
                ->references('cod_habitacion')
                ->on('habitaciones')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camas');
    }
};
