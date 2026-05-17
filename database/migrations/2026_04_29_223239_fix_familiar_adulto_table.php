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
        if (Schema::hasTable('familiar_adulto')) {
            Schema::table('familiar_adulto', function (Blueprint $table) {
                // Agregar columnas si no existen
                if (!Schema::hasColumn('familiar_adulto', 'cod_fam')) {
                    $table->string('cod_fam', 20)->nullable();
                }
                if (!Schema::hasColumn('familiar_adulto', 'cod_am')) {
                    $table->string('cod_am', 20)->nullable();
                }
                if (!Schema::hasColumn('familiar_adulto', 'parentesco_vinculo')) {
                    $table->string('parentesco_vinculo')->nullable();
                }
                if (!Schema::hasColumn('familiar_adulto', 'es_responsable')) {
                    $table->boolean('es_responsable')->default(false);
                }
                if (!Schema::hasColumn('familiar_adulto', 'estado')) {
                    $table->string('estado')->default('ACTIVO');
                }
                if (!Schema::hasColumn('familiar_adulto', 'observaciones')) {
                    $table->text('observaciones')->nullable();
                }

                // Índice único para evitar duplicidad de vínculos
                $table->unique(['cod_fam', 'cod_am'], 'uidx_familiar_adulto');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('familiar_adulto', function (Blueprint $table) {
            $table->dropUnique('uidx_familiar_adulto');
            $table->dropColumn(['cod_fam', 'cod_am', 'parentesco_vinculo', 'es_responsable', 'estado', 'observaciones']);
        });
    }
};
