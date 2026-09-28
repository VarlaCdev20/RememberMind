<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->string('cod_area', 20)->primary();
            $table->string('nombre', 80)->unique();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
