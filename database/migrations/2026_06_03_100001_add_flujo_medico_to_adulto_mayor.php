<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            if (! Schema::hasColumn('adulto_mayor', 'motivo_ingreso')) {
                $table->text('motivo_ingreso')->nullable()->after('hora_ing');
            }
            if (! Schema::hasColumn('adulto_mayor', 'procedencia_ingreso')) {
                $table->string('procedencia_ingreso')->nullable()->after('motivo_ingreso');
                // Ej: DOMICILIO, HOSPITAL, CLINICA, TRASLADO, OTRO
            }
        });
    }

    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->dropColumnIfExists('motivo_ingreso');
            $table->dropColumnIfExists('procedencia_ingreso');
        });
    }
};
