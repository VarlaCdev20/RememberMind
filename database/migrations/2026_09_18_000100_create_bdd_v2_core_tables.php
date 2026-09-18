<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->string('cod_usuario', 20)->primary();
            $table->string('correo', 120)->unique();
            $table->string('contrasena', 255);
            $table->string('foto', 255)->nullable();
            $table->string('estado', 20);
        });

        Schema::create('personal', function (Blueprint $table) {
            $table->string('cod_personal', 20)->primary();
            $table->string('cod_usuario', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->unique();
            $table->string('expedicion_documento', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('profesion', 80);
            $table->string('especialidad', 120)->nullable();
            $table->string('matricula_profesional', 50)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->string('cod_area', 20)->primary();
            $table->string('nombre', 80)->unique();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20);
        });

        Schema::create('turnos', function (Blueprint $table) {
            $table->string('cod_turno', 20)->primary();
            $table->string('nombre', 50)->unique();
            $table->time('hora_inicio');
            $table->time('hora_cierre');
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });

        Schema::create('contactos', function (Blueprint $table) {
            $table->string('cod_contacto', 20)->primary();
            $table->string('cod_usuario', 20)->nullable()->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('residentes', function (Blueprint $table) {
            $table->string('cod_residente', 20)->primary();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->nullable()->unique();
            $table->string('complemento_documento', 10)->nullable();
            $table->string('expedicion_documento', 20)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('genero', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('nivel_educativo', 80)->nullable();
            $table->string('grupo_sanguineo', 5)->nullable();
            $table->string('factor_rh', 5)->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });

        Schema::create('habitaciones', function (Blueprint $table) {
            $table->string('cod_habitacion', 20)->primary();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 80)->nullable();
            $table->string('tipo', 40)->nullable();
            $table->string('piso', 30)->nullable();
            $table->smallInteger('capacidad');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });

        Schema::create('camas', function (Blueprint $table) {
            $table->string('cod_cama', 20)->primary();
            $table->string('cod_habitacion', 20);
            $table->string('codigo', 30)->unique();
            $table->string('tipo', 40)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_habitacion')->references('cod_habitacion')->on('habitaciones')->restrictOnDelete();
        });

        Schema::create('tipos_estudio_clinico', function (Blueprint $table) {
            $table->string('cod_tipo_estudio', 20)->primary();
            $table->string('nombre', 120)->unique();
            $table->string('categoria', 50);
            $table->text('descripcion')->nullable();
            $table->boolean('requiere_componentes');
            $table->boolean('requiere_informe');
            $table->string('estado', 20);
        });

        Schema::create('medicamentos', function (Blueprint $table) {
            $table->string('cod_medicamento', 20)->primary();
            $table->string('nombre_generico', 120);
            $table->string('nombre_comercial', 120)->nullable();
            $table->string('concentracion', 60)->nullable();
            $table->string('forma_farmaceutica', 60)->nullable();
            $table->string('unidad', 30)->nullable();
            $table->string('via_predeterminada', 60)->nullable();
            $table->boolean('control_especial');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
        });

        Schema::create('instrumentos', function (Blueprint $table) {
            $table->string('cod_instrumento', 20)->primary();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 160);
            $table->string('tipo', 50);
            $table->string('version', 30)->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->string('estado', 20);
        });

        Schema::create('jornadas', function (Blueprint $table) {
            $table->string('cod_jornada', 20)->primary();
            $table->string('cod_turno', 20);
            $table->string('cod_usuario_apertura', 20)->nullable();
            $table->string('cod_usuario_cierre', 20)->nullable();
            $table->date('fecha_jornada');
            $table->string('estado', 20);
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos')->restrictOnDelete();
            $table->foreign('cod_usuario_apertura')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_usuario_cierre')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('asignaciones_personal', function (Blueprint $table) {
            $table->string('cod_asignacion_personal', 20)->primary();
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_area', 20);
            $table->string('funcion', 80)->nullable();
            $table->string('tipo_asignacion', 30);
            $table->dateTime('fecha_asignacion');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
        });

        Schema::create('preadmisiones', function (Blueprint $table) {
            $table->string('cod_preadmision', 20)->primary();
            $table->string('cod_contacto', 20)->nullable();
            $table->string('cod_usuario_registro', 20);
            $table->string('cod_usuario_revision', 20)->nullable();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 80);
            $table->string('apellido_materno', 80)->nullable();
            $table->string('numero_documento', 30)->nullable();
            $table->string('expedicion_documento', 20)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('genero', 20)->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->text('motivo_ingreso');
            $table->string('procedencia', 120)->nullable();
            $table->string('tipo_ingreso', 50)->nullable();
            $table->string('permanencia', 50)->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->text('descripcion_caso')->nullable();
            $table->dateTime('fecha_solicitud');
            $table->dateTime('fecha_revision')->nullable();
            $table->string('estado', 20);
            $table->text('motivo_rechazo')->nullable();
            $table->index(['numero_documento', 'estado']);
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_usuario_revision')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('admisiones', function (Blueprint $table) {
            $table->string('cod_admision', 20)->primary();
            $table->string('cod_preadmision', 20)->nullable();
            $table->string('cod_residente', 20);
            $table->string('cod_usuario_registro', 20);
            $table->dateTime('fecha_hora_admision');
            $table->string('tipo_ingreso', 50)->nullable();
            $table->string('procedencia', 120)->nullable();
            $table->text('motivo_ingreso');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_preadmision')->references('cod_preadmision')->on('preadmisiones')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('residentes_contactos', function (Blueprint $table) {
            $table->string('cod_residente_contacto', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_contacto', 20);
            $table->string('parentesco', 40);
            $table->boolean('responsable_principal');
            $table->boolean('contacto_emergencia');
            $table->boolean('autoriza_informacion');
            $table->boolean('autoriza_salida');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'cod_contacto', 'estado']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
        });

        Schema::create('historial_estados_residente', function (Blueprint $table) {
            $table->string('cod_historial_estado', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_usuario_registro', 20);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->dateTime('fecha_hora');
            $table->text('motivo')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('ocupaciones_cama', function (Blueprint $table) {
            $table->string('cod_ocupacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_cama', 20);
            $table->string('cod_admision', 20);
            $table->string('cod_usuario_registro', 20);
            $table->dateTime('fecha_hora_asignacion');
            $table->dateTime('fecha_hora_liberacion')->nullable();
            $table->text('motivo_liberacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_cama', 'estado']);
            $table->index(['cod_residente', 'estado']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_cama')->references('cod_cama')->on('camas')->restrictOnDelete();
            $table->foreign('cod_admision')->references('cod_admision')->on('admisiones')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('documentos', function (Blueprint $table) {
            $table->string('cod_documento', 20)->primary();
            $table->string('cod_preadmision', 20)->nullable();
            $table->string('cod_residente', 20)->nullable();
            $table->string('cod_usuario', 20)->nullable();
            $table->string('cod_contacto', 20)->nullable();
            $table->string('cod_documento_anterior', 20)->nullable();
            $table->string('cod_usuario_validacion', 20)->nullable();
            $table->string('tipo_documento', 60);
            $table->string('nombre', 160);
            $table->string('ruta_archivo', 500);
            $table->string('tipo_archivo', 80);
            $table->string('hash_archivo', 128);
            $table->date('fecha_vencimiento')->nullable();
            $table->dateTime('fecha_validacion')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_preadmision')->references('cod_preadmision')->on('preadmisiones')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_contacto')->references('cod_contacto')->on('contactos')->restrictOnDelete();
            $table->foreign('cod_documento_anterior')->references('cod_documento')->on('documentos')->restrictOnDelete();
            $table->foreign('cod_usuario_validacion')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('consentimientos', function (Blueprint $table) {
            $table->string('cod_consentimiento', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_admision', 20);
            $table->string('cod_residente_contacto', 20)->nullable();
            $table->string('cod_documento', 20)->nullable();
            $table->string('cod_usuario_registro', 20);
            $table->string('tipo_consentimiento', 80);
            $table->boolean('firma_residente');
            $table->dateTime('fecha_consentimiento');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_admision')->references('cod_admision')->on('admisiones')->restrictOnDelete();
            $table->foreign('cod_residente_contacto')->references('cod_residente_contacto')->on('residentes_contactos')->restrictOnDelete();
            $table->foreign('cod_documento')->references('cod_documento')->on('documentos')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        Schema::create('atenciones', function (Blueprint $table) {
            $table->string('cod_atencion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_area', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_atencion', 60);
            $table->text('motivo')->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        foreach ([
            'atenciones', 'consentimientos', 'documentos', 'ocupaciones_cama',
            'historial_estados_residente', 'residentes_contactos', 'admisiones',
            'preadmisiones', 'asignaciones_personal', 'jornadas', 'instrumentos',
            'medicamentos', 'tipos_estudio_clinico', 'camas', 'habitaciones',
            'residentes', 'contactos', 'turnos', 'areas', 'personal', 'usuarios',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
