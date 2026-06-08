<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_adulto', function (Blueprint $table) {
            $table->string('cod_est_adul', 20)->primary();
            $table->string('estado', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_adulto');
    }
};