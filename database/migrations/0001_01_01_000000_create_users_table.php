<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('cod_usu', 20)->primary();
            $table->string('nombres', 100);
            $table->string('ap_paterno', 80)->nullable();
            $table->string('ap_materno', 80)->nullable();
            $table->string('pais_documento', 100)->nullable();
            $table->string('tipo_documento', 50)->nullable();
            $table->string('numero_documento', 50)->nullable();
            $table->string('expedido', 50)->nullable();
            $table->string('correo', 120)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->string('pais_telefono', 50)->nullable();
            $table->string('codigo_telefono', 10)->nullable();
            $table->string('genero', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('foto_de_perfil', 255)->nullable();
            $table->string('estado', 100)->default('ACTIVO');
            $table->string('acceso_sistema', 20)->default('SI');
            $table->dateTime('ultimo_acceso')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('debe_cambiar_password')->default(false);
            $table->dateTime('password_changed_at')->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('calle', 100)->nullable();
            $table->string('nro_domicilio', 20)->nullable();
            $table->string('contacto_emergencia', 150)->nullable();
            $table->string('parentesco_emergencia', 100)->nullable();
            $table->string('celular_emergencia', 20)->nullable();
            $table->string('ap_paterno_emergencia', 80)->nullable();
            $table->string('ap_materno_emergencia', 80)->nullable();
            $table->string('tipo_vinculacion', 100)->nullable();
            $table->unsignedBigInteger('current_team_id')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('correo', 120)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 20)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};