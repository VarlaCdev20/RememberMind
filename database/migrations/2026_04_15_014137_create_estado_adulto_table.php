<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_adulto', function (Blueprint $table) {
            $table->increments('cod_est_adul');

            $table->string('estado', 100);
            $table->date('fecha_in')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_adulto');
    }
};