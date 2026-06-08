<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familiar_adulto', function (Blueprint $table) {
            $table->id();
            $table->string('cod_fam', 20);
            $table->string('cod_am', 20);
            $table->string('parentesco_vinculo', 100)->nullable();
            $table->boolean('es_responsable')->default(false);
            $table->string('estado', 50)->default('ACTIVO');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['cod_fam', 'cod_am'], 'uidx_familiar_adulto');

            $table->foreign('cod_fam')
                ->references('cod_fam')
                ->on('familiares')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('familiar_adulto');
    }
};
