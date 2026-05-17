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
        Schema::table('adulto_mayor', function (Blueprint $table) {
            // Identidad
            if (!Schema::hasColumn('adulto_mayor', 'complemento_ci')) $table->string('complemento_ci', 2)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'expedicion_ci')) $table->string('expedicion_ci', 2)->nullable();

            // Contacto
            if (!Schema::hasColumn('adulto_mayor', 'celular')) $table->string('celular', 8)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'telefono_fijo')) $table->string('telefono_fijo', 10)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'departamento_residencia')) $table->string('departamento_residencia', 50)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'ciudad_municipio')) $table->string('ciudad_municipio', 100)->nullable();

            // Información médica base
            if (!Schema::hasColumn('adulto_mayor', 'grupo_sanguineo')) $table->string('grupo_sanguineo', 3)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'factor_rh')) $table->string('factor_rh', 1)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'alergias')) $table->text('alergias')->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'seguro_salud')) $table->string('seguro_salud', 80)->nullable();

            // Responsable / contacto emergencia
            if (!Schema::hasColumn('adulto_mayor', 'contacto_emergencia_nombre')) $table->string('contacto_emergencia_nombre', 150)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'contacto_emergencia_parentesco')) $table->string('contacto_emergencia_parentesco', 80)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'contacto_emergencia_celular')) $table->string('contacto_emergencia_celular', 8)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'contacto_emergencia_direccion')) $table->string('contacto_emergencia_direccion', 200)->nullable();
            if (!Schema::hasColumn('adulto_mayor', 'responsable_principal')) $table->boolean('responsable_principal')->default(false);
            if (!Schema::hasColumn('adulto_mayor', 'autorizado_informacion_medica')) $table->boolean('autorizado_informacion_medica')->default(false);
            if (!Schema::hasColumn('adulto_mayor', 'consentimiento_datos')) $table->boolean('consentimiento_datos')->default(false);
        });

        // Drop the old unique index and add the new one
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $indexes = Schema::getIndexes('adulto_mayor');
            $indexNames = array_column($indexes, 'name');
            
            if (in_array('adulto_mayor_ci_unique', $indexNames)) {
                $table->dropUnique('adulto_mayor_ci_unique');
            }
            
            if (in_array('adulto_mayor_ci_expedicion_ci_complemento_ci_unique', $indexNames)) {
                $table->dropUnique('adulto_mayor_ci_expedicion_ci_complemento_ci_unique');
            }
            
            $table->unique(['ci', 'expedicion_ci', 'complemento_ci']);
        });
    }

    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->dropUnique(['ci', 'expedicion_ci', 'complemento_ci']);
            $table->unique('ci');

            $columns = [
                'complemento_ci', 'expedicion_ci', 'celular', 'telefono_fijo',
                'departamento_residencia', 'ciudad_municipio', 'grupo_sanguineo',
                'factor_rh', 'alergias', 'seguro_salud', 'contacto_emergencia_nombre',
                'contacto_emergencia_parentesco', 'contacto_emergencia_celular',
                'contacto_emergencia_direccion', 'responsable_principal',
                'autorizado_informacion_medica', 'consentimiento_datos'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('adulto_mayor', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
