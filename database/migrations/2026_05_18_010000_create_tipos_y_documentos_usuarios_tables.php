<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar la tabla de documentos anterior si existe
        Schema::dropIfExists('documentos_usuarios');

        // 2. Crear tabla de Tipos de Documentos de Usuario
        Schema::create('tipos_documentos_usuario', function (Blueprint $table) {
            $table->string('cod_tipo_doc', 20)->primary(); // TDU_0001
            $table->string('nombre', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->json('aplica_roles')->nullable(); // ['admin', 'personal_salud', ...]
            $table->boolean('obligatorio')->default(false);
            $table->boolean('requiere_vencimiento')->default(false);
            $table->boolean('requiere_validacion')->default(true);
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, INACTIVO
            $table->integer('orden')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Crear tabla de Documentos de Usuarios con relaciones completas
        Schema::create('documentos_usuarios', function (Blueprint $table) {
            $table->string('cod_doc_usu', 20)->primary(); // DUS_0001
            
            $table->string('cod_usu', 20);
            $table->string('cod_tipo_doc', 20)->nullable();
            
            $table->string('tipo_documento', 100)->nullable(); // Nombre del tipo en caso de no usar catalogo
            $table->string('nombre_documento', 150);
            $table->string('archivo', 255);
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 10)->nullable();
            $table->integer('tamanio')->nullable(); // En bytes
            
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            
            $table->string('estado', 25)->default('PENDIENTE'); // PENDIENTE, CARGADO, VALIDADO, OBSERVADO, VENCIDO, REEMPLAZADO, ANULADO
            $table->text('observaciones')->nullable();
            $table->text('motivo_observacion')->nullable();
            
            $table->string('subido_por', 20)->nullable();
            $table->string('validado_por', 20)->nullable();
            $table->timestamp('fecha_validacion')->nullable();
            
            $table->string('reemplaza_a', 20)->nullable();
            
            $table->string('creado_por', 20)->nullable();
            $table->string('actualizado_por', 20)->nullable();

            // Relaciones
            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_tipo_doc')
                ->references('cod_tipo_doc')
                ->on('tipos_documentos_usuario')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('subido_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('validado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Agregar llave foránea autorreferenciable por separado
        Schema::table('documentos_usuarios', function (Blueprint $table) {
            $table->foreign('reemplaza_a')
                ->references('cod_doc_usu')
                ->on('documentos_usuarios')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_usuarios', function (Blueprint $table) {
            $table->dropForeign(['reemplaza_a']);
        });
        Schema::dropIfExists('documentos_usuarios');
        Schema::dropIfExists('tipos_documentos_usuario');
    }
};
