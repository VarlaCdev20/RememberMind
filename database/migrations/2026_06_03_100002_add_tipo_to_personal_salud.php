<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_salud', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_salud', 'tipo_personal_salud')) {
                $table->string('tipo_personal_salud', 30)->nullable()->after('cod_esp');
                // MEDICO, ENFERMERO, PSICOLOGO, FISIOTERAPEUTA, OTRO
            }
            if (! Schema::hasColumn('personal_salud', 'subtipo_enfermeria')) {
                $table->string('subtipo_enfermeria', 40)->nullable()->after('tipo_personal_salud');
                // GENERAL_ADMISION, ESPECIALIZADO_TURNO
            }
        });
    }

    public function down(): void
    {
        Schema::table('personal_salud', function (Blueprint $table) {
            $table->dropColumnIfExists('subtipo_enfermeria');
            $table->dropColumnIfExists('tipo_personal_salud');
        });
    }
};
