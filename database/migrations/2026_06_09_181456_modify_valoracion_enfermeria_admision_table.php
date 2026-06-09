<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valoracion_enfermeria_admision', function (Blueprint $table) {
            // Modificar cod_am para permitir null
            $table->string('cod_am', 20)->nullable()->change();

            // Agregar columna cod_pre (nullable, 20)
            if (!Schema::hasColumn('valoracion_enfermeria_admision', 'cod_pre')) {
                $table->string('cod_pre', 20)->nullable()->after('cod_am');
                
                $table->foreign('cod_pre')
                    ->references('cod_pre')
                    ->on('preadmisiones')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('valoracion_enfermeria_admision', function (Blueprint $table) {
            // Remover FK de cod_pre y luego la columna
            if (Schema::hasColumn('valoracion_enfermeria_admision', 'cod_pre')) {
                // En SQLite no siempre se pueden remover FKs directamente así,
                // pero Laravel maneja la abstracción o lo ignora según driver.
                // Usamos try-catch por si acaso para no romper los rollbacks de pruebas.
                try {
                    $table->dropForeign(['cod_pre']);
                } catch (\Exception $e) {
                    // Ignorar si no se puede borrar FK directamente
                }
                $table->dropColumn('cod_pre');
            }

            // Restaurar cod_am a NOT NULL (solo si no hay registros huérfanos con NULL)
            try {
                $table->string('cod_am', 20)->nullable(false)->change();
            } catch (\Exception $e) {
                // Mantener nullable si falla por datos existentes
            }
        });
    }
};
