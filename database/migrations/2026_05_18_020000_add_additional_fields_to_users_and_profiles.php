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
        // 1. Agregar campos adicionales a la tabla 'users'
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'direccion')) {
                $table->string('direccion', 255)->nullable()->after('acceso_sistema');
            }
            if (!Schema::hasColumn('users', 'zona')) {
                $table->string('zona', 100)->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('users', 'ciudad')) {
                $table->string('ciudad', 100)->nullable()->after('zona');
            }
            if (!Schema::hasColumn('users', 'contacto_emergencia')) {
                $table->string('contacto_emergencia', 150)->nullable()->after('ciudad');
            }
            if (!Schema::hasColumn('users', 'parentesco_emergencia')) {
                $table->string('parentesco_emergencia', 100)->nullable()->after('contacto_emergencia');
            }
            if (!Schema::hasColumn('users', 'celular_emergencia')) {
                $table->string('celular_emergencia', 20)->nullable()->after('parentesco_emergencia');
            }
            if (!Schema::hasColumn('users', 'tipo_vinculacion')) {
                $table->string('tipo_vinculacion', 50)->nullable()->after('celular_emergencia'); // CONTRATO, VOLUNTARIADO, CONVENIO, FAMILIAR, OTRO
            }
        });

        // 2. Agregar campos adicionales a la tabla 'personal_salud'
        Schema::table('personal_salud', function (Blueprint $table) {
            if (!Schema::hasColumn('personal_salud', 'institucion_formacion')) {
                $table->string('institucion_formacion', 255)->nullable();
            }
        });

        // 3. Agregar campos adicionales a la tabla 'voluntarios'
        Schema::table('voluntarios', function (Blueprint $table) {
            if (!Schema::hasColumn('voluntarios', 'disponibilidad_inicial')) {
                $table->string('disponibilidad_inicial', 150)->nullable();
            }
            if (!Schema::hasColumn('voluntarios', 'area_apoyo_preferente')) {
                $table->string('area_apoyo_preferente', 150)->nullable();
            }
        });

        // 4. Agregar campos adicionales a la tabla 'familiares'
        Schema::table('familiares', function (Blueprint $table) {
            if (!Schema::hasColumn('familiares', 'observacion_vinculo')) {
                $table->string('observacion_vinculo', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'direccion',
                'zona',
                'ciudad',
                'contacto_emergencia',
                'parentesco_emergencia',
                'celular_emergencia',
                'tipo_vinculacion'
            ]);
        });

        Schema::table('personal_salud', function (Blueprint $table) {
            $table->dropColumn(['institucion_formacion']);
        });

        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn(['disponibilidad_inicial', 'area_apoyo_preferente']);
        });

        Schema::table('familiares', function (Blueprint $table) {
            $table->dropColumn(['observacion_vinculo']);
        });
    }
};
