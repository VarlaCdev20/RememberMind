<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_salud_adulto', function (Blueprint $table) {
            $table->id();
            $table->string('cod_am', 10);
            $table->unsignedInteger('cod_per_sal');
            $table->string('asignado_por', 20);
            $table->string('tipo_asignacion', 30);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->default('activa');
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('cod_per_sal')
                ->references('cod_per_sal')
                ->on('personal_salud')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('asignado_por')
                ->references('cod_usu')
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['cod_am', 'estado']);
            $table->index(['cod_per_sal', 'estado']);
            $table->index(['tipo_asignacion', 'estado']);
            $table->index('fecha_inicio');
        });

        DB::statement("
            CREATE UNIQUE INDEX asignaciones_salud_adulto_activa_unica_idx
            ON asignaciones_salud_adulto (cod_am, cod_per_sal, tipo_asignacion)
            WHERE estado = 'activa'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS asignaciones_salud_adulto_activa_unica_idx');
        Schema::dropIfExists('asignaciones_salud_adulto');
    }
};
