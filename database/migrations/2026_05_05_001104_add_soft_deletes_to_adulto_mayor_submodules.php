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
        Schema::table('obs_adulto', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('atenciones_adulto', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('actividades_adulto', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('documentos_adulto_mayor', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('evaluaciones_cognitivas', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obs_adulto', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('atenciones_adulto', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('actividades_adulto', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('documentos_adulto_mayor', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('evaluaciones_cognitivas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
