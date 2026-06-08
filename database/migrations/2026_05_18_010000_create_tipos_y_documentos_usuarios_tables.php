<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_documentos_usuario', function (Blueprint $table) {
            $table->string('cod_tipo_doc', 20)->primary();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->json('aplica_roles')->nullable();
            $table->boolean('obligatorio')->default(false);
            $table->boolean('requiere_vencimiento')->default(false);
            $table->boolean('requiere_validacion')->default(false);
            $table->string('estado', 50)->default('ACTIVO');
            $table->integer('orden')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('documentos_usuarios', function (Blueprint $table) {
            $table->string('cod_doc_usu', 20)->primary();
            $table->string('cod_usu', 20);
            $table->string('cod_tipo_doc', 20);
            $table->string('archivo_path', 255);
            $table->string('nombre_original', 150);
            $table->dateTime('fecha_subida');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado', 50)->default('PENDIENTE');
            $table->string('validado_por', 20)->nullable();
            $table->dateTime('fecha_validacion')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_tipo_doc')
                ->references('cod_tipo_doc')
                ->on('tipos_documentos_usuario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('validado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_usuarios');
        Schema::dropIfExists('tipos_documentos_usuario');
    }
};
