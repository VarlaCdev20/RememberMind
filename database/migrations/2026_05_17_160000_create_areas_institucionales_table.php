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
        Schema::create('areas_institucionales', function (Blueprint $table) {
            $table->string('cod_area', 20)->primary(); // ARE_0001, etc.
            $table->string('nombre', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('tipo_area', 50); // E.g. Medica, Psicologia, Administrativa
            $table->text('descripcion')->nullable();
            $table->string('responsable_id', 20)->nullable(); // FK to users.cod_usu
            $table->jsonb('roles_sugeridos')->nullable(); // JSON Array for roles suggestions
            $table->jsonb('modulos_relacionados')->nullable(); // JSON Array for system modules
            $table->string('color', 20)->nullable();
            $table->string('icono', 50)->nullable();
            $table->string('estado', 20)->default('ACTIVA'); // ACTIVA, INACTIVA
            $table->integer('orden')->default(0);
            $table->text('observaciones')->nullable();
            $table->string('creado_por', 20)->nullable(); // FK to users.cod_usu
            $table->string('actualizado_por', 20)->nullable(); // FK to users.cod_usu
            $table->timestamps();
            $table->softDeletes(); // Soft deletes as requested

            // Foreign Key to users.cod_usu for responsable, creado_por, actualizado_por
            $table->foreign('responsable_id')->references('cod_usu')->on('users')->onDelete('set null');
            $table->foreign('creado_por')->references('cod_usu')->on('users')->onDelete('set null');
            $table->foreign('actualizado_por')->references('cod_usu')->on('users')->onDelete('set null');
        });

        // Add cod_area to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('cod_area', 20)->nullable()->after('estado');
            $table->foreign('cod_area')->references('cod_area')->on('areas_institucionales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cod_area']);
            $table->dropColumn('cod_area');
        });

        Schema::dropIfExists('areas_institucionales');
    }
};
