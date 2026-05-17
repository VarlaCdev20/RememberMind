<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pais_documento')) {
                $table->string('pais_documento', 50)->nullable()->after('ap_materno');
            }
            if (!Schema::hasColumn('users', 'tipo_documento')) {
                $table->string('tipo_documento', 50)->nullable()->after('pais_documento');
            }
            if (!Schema::hasColumn('users', 'numero_documento')) {
                $table->string('numero_documento', 30)->nullable()->unique()->after('tipo_documento');
            }
            if (!Schema::hasColumn('users', 'expedido')) {
                $table->string('expedido', 10)->nullable()->after('numero_documento');
            }

            if (!Schema::hasColumn('users', 'pais_telefono')) {
                $table->string('pais_telefono', 50)->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('users', 'codigo_telefono')) {
                $table->string('codigo_telefono', 10)->nullable()->after('pais_telefono');
            }

            if (!Schema::hasColumn('users', 'genero')) {
                $table->string('genero', 20)->nullable()->after('codigo_telefono');
            }
            if (!Schema::hasColumn('users', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('genero');
            }

            if (!Schema::hasColumn('users', 'acceso_sistema')) {
                $table->string('acceso_sistema', 20)->default('HABILITADO')->after('estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pais_documento',
                'tipo_documento',
                'numero_documento',
                'expedido',
                'pais_telefono',
                'codigo_telefono',
                'genero',
                'fecha_nacimiento',
                'acceso_sistema',
            ]);
        });
    }
};
