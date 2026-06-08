<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas_geriatricas', function (Blueprint $table) {
            $table->string('cod_area', 20)->primary(); // ARE_COG, ARE_AFE, etc.
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, INACTIVO
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas_geriatricas');
    }
};
