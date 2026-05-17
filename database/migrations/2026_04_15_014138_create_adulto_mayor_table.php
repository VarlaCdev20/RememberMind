<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adulto_mayor', function (Blueprint $table) {
            $table->increments('cod_am');

            $table->string('nombres', 100);
            $table->string('ap_paterno', 80);
            $table->string('ap_materno', 80)->nullable();

            $table->string('ci', 20)->nullable()->unique();

            $table->date('fecha_nac');
            $table->string('genero', 100);
            $table->string('estado_civil', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('calle', 150)->nullable();

            $table->date('fecha_ing');
            $table->string('tipo_ing', 100);
            $table->string('permanencia', 50)->nullable();
            $table->string('nivel_educat', 100)->nullable();

            $table->text('observaciones')->nullable();

            $table->unsignedInteger('cod_est_adul');

            $table->foreign('cod_est_adul')
                ->references('cod_est_adul')
                ->on('estado_adulto')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->dropForeign(['cod_est_adul']);
        });

        Schema::dropIfExists('adulto_mayor');
    }
};