<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adulto_mayor', function (Blueprint $table) {
            $table->string('cod_am', 20)->primary();
            $table->string('nombres', 100);
            $table->string('ap_paterno', 80);
            $table->string('ap_materno', 80)->nullable();
            $table->string('ci', 20)->nullable()->unique();
            $table->string('complemento_ci', 10)->nullable();
            $table->string('expedicion_ci', 50)->nullable();
            $table->date('fecha_nac');
            $table->string('genero', 100);
            $table->string('estado_civil', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('tiene_celular', 10)->default('NO');
            $table->string('celular', 20)->nullable();
            $table->string('sabe_usar_whatsapp', 10)->default('NO');
            $table->string('telefono_fijo', 20)->nullable();
            $table->string('departamento_residencia', 100)->nullable();
            $table->string('ciudad_municipio', 100)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('calle', 150)->nullable();
            $table->date('fecha_ing');
            $table->time('hora_ing')->nullable();
            $table->string('tipo_ing', 100);
            $table->string('permanencia', 50)->nullable();
            $table->string('nivel_educat', 100)->nullable();
            $table->string('grupo_sanguineo', 10)->nullable();
            $table->string('factor_rh', 5)->nullable();
            $table->text('alergias')->nullable();
            $table->string('seguro_salud', 100)->nullable();
            $table->string('contacto_emergencia_nombre', 150)->nullable();
            $table->string('contacto_emergencia_parentesco', 100)->nullable();
            $table->string('contacto_emergencia_celular', 20)->nullable();
            $table->string('contacto_emergencia_direccion', 255)->nullable();
            $table->string('responsable_principal', 150)->nullable();
            $table->string('autorizado_informacion_medica', 150)->nullable();
            $table->boolean('consentimiento_datos')->default(false);
            $table->text('observaciones')->nullable();
            $table->string('cod_est_adul', 20);
            $table->string('foto', 255)->nullable();
            $table->timestamp('archivado_en')->nullable();
            $table->text('motivo_archivado')->nullable();
            $table->text('motivo_ingreso')->nullable();
            $table->string('procedencia_ingreso', 150)->nullable();
            $table->string('cod_habitacion', 20)->nullable();
            $table->string('cod_cama', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_est_adul')
                ->references('cod_est_adul')
                ->on('estado_adulto')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_habitacion')
                ->references('cod_habitacion')
                ->on('habitaciones')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('cod_cama')
                ->references('cod_cama')
                ->on('camas')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adulto_mayor');
    }
};