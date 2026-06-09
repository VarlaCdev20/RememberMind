<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asignaciones_plazas_enfermeria')) {
            return;
        }

        Schema::create('asignaciones_plazas_enfermeria', function (Blueprint $table) {
            $table->id();
            $table->string('plaza', 10); // e.g., 'E01' to 'E12'
            $table->string('cod_usu', 20)->nullable(); // Nurse user ID
            $table->string('tipo', 20); // 'TITULAR', 'REEMPLAZO', 'APOYO', 'DESCANSO'
            $table->date('fecha')->nullable(); // Null for permanent titular, date for temporal
            $table->text('motivo')->nullable(); // For replacements or rests
            $table->timestamps();

            // Foreign Key
            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // Indexes for speed and lookup
            $table->index(['plaza', 'fecha']);
            $table->index('cod_usu');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_plazas_enfermeria');
    }
};
