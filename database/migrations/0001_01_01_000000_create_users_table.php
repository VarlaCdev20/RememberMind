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
            $table->string('ap_paterno', 80);
            $table->string('ap_materno', 80)->nullable();

            $table->string('correo', 120)->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->string('telefono', 20)->nullable();
            $table->string('foto_de_perfil', 255)->nullable();

            $table->string('estado', 100)->default('ACTIVO');
            $table->dateTime('ultimo_acceso')->nullable();

            $table->rememberToken();
            $table->unsignedBigInteger('current_team_id')->nullable();

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