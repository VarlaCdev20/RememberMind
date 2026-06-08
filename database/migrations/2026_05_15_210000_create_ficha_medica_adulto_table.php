<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_medica_adulto', function (Blueprint $table) {
            $table->string('cod_ficha_medica', 20)->primary();
            $table->string('cod_am', 20);
            $table->boolean('hipertension')->default(false);
            $table->boolean('diabetes')->default(false);
            $table->boolean('problemas_cardiacos')->default(false);
            $table->boolean('acv')->default(false);
            $table->boolean('parkinson')->default(false);
            $table->boolean('epilepsia')->default(false);
            $table->boolean('alzheimer_diagnosticado')->default(false);
            $table->boolean('depresion')->default(false);
            $table->boolean('ansiedad')->default(false);
            $table->boolean('problemas_sueno')->default(false);
            $table->boolean('problemas_visuales')->default(false);
            $table->boolean('problemas_auditivos')->default(false);
            $table->boolean('dolor_cronico')->default(false);
            $table->text('alergias')->nullable();
            $table->text('restricciones_alimentarias')->nullable();
            $table->text('hospitalizaciones')->nullable();
            $table->text('cirugias')->nullable();
            $table->text('observacion_medica')->nullable();
            $table->string('registrado_por', 20)->nullable();
            $table->string('estado', 50)->default('ACTIVO');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_medica_adulto');
    }
};
