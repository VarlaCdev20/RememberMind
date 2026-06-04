<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loc_departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('loc_municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('loc_departamentos')->onDelete('cascade');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['departamento_id', 'nombre']);
        });

        Schema::create('loc_zonas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('loc_municipios')->onDelete('cascade');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['municipio_id', 'nombre']);
        });

        Schema::create('loc_calles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zona_id')->constrained('loc_zonas')->onDelete('cascade');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['zona_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loc_calles');
        Schema::dropIfExists('loc_zonas');
        Schema::dropIfExists('loc_municipios');
        Schema::dropIfExists('loc_departamentos');
    }
};
