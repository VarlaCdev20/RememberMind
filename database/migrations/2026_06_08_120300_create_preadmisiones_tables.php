<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('preadmisiones')) {
            Schema::create('preadmisiones', function (Blueprint $table) {
                $table->string('cod_pre', 20)->primary();
                $table->string('estado', 40)->default('PREADMISION_ASIGNADA');
                $table->date('fecha_solicitud');
                $table->timestamp('fecha_asignacion')->nullable();

                $table->string('nombres', 100);
                $table->string('ap_paterno', 80);
                $table->string('ap_materno', 80)->nullable();
                $table->string('ci', 20);
                $table->string('expedicion_ci', 10)->nullable();
                $table->date('fecha_nac');
                $table->string('genero', 30);
                $table->string('estado_civil', 50)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('celular', 30)->nullable();

                $table->string('departamento_residencia', 80)->nullable();
                $table->string('ciudad_municipio', 120)->nullable();
                $table->string('zona', 120)->nullable();
                $table->string('calle', 180)->nullable();
                $table->text('direccion_referencia')->nullable();

                $table->string('familiar_nombres', 120);
                $table->string('familiar_ap_paterno', 80)->nullable();
                $table->string('familiar_ap_materno', 80)->nullable();
                $table->string('familiar_ci', 20)->nullable();
                $table->string('familiar_parentesco', 80);
                $table->string('familiar_celular', 30);
                $table->string('familiar_correo', 140)->nullable();
                $table->text('familiar_direccion')->nullable();

                $table->string('motivo_ingreso', 120)->nullable();
                $table->string('procedencia_ingreso', 120)->nullable();
                $table->string('tipo_ingreso', 80)->nullable();
                $table->string('permanencia', 80)->nullable();
                $table->string('prioridad', 30)->nullable();
                $table->text('descripcion_caso')->nullable();

                $table->boolean('documentos_iniciales_completos')->default(false);
                $table->boolean('documentos_institucionales_generados')->default(false);
                $table->string('enfermero_asignado', 20)->nullable();
                $table->string('creado_por', 20)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('enfermero_asignado')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
                $table->foreign('creado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
                $table->index(['estado', 'fecha_solicitud']);
                $table->unique(['ci', 'expedicion_ci'], 'uidx_preadmisiones_ci_exp');
            });
        }

        if (! Schema::hasTable('documentos_preadmision')) {
            Schema::create('documentos_preadmision', function (Blueprint $table) {
                $table->string('cod_doc_pre', 20)->primary();
                $table->string('cod_pre', 20);
                $table->string('tipo_documento', 80);
                $table->string('nombre_documento', 180);
                $table->string('archivo_path', 255)->nullable();
                $table->string('nombre_original', 180)->nullable();
                $table->boolean('es_institucional')->default(false);
                $table->boolean('obligatorio')->default(false);
                $table->string('estado', 40)->default('PENDIENTE');
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('cod_pre')->references('cod_pre')->on('preadmisiones')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['cod_pre', 'tipo_documento']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_preadmision');
        Schema::dropIfExists('preadmisiones');
    }
};
