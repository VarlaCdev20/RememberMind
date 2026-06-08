<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('acciones_alerta')) {
            return;
        }

        Schema::create('acciones_alerta', function (Blueprint $table) {
            $table->string('cod_accion_alerta', 20)->primary();
            $table->string('cod_alerta', 20);
            $table->text('accion');
            $table->string('responsable_id', 20)->nullable(); // FK users
            $table->timestamp('fecha_accion');
            $table->string('estado', 20)->default('PENDIENTE');
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('cod_alerta')
                ->references('cod_alerta')
                ->on('alertas_adulto')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('responsable_id')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_alerta', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acciones_alerta');
    }
};
