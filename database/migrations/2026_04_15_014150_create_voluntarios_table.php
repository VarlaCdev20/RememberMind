<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voluntarios', function (Blueprint $table) {
            $table->increments('cod_vol');

            $table->date('fecha_ing');
            $table->string('area_apoyo', 100);
            $table->string('estado', 100);
            $table->text('observaciones')->nullable();

            $table->string('cod_usu', 20);

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropForeign(['cod_usu']);
        });

        Schema::dropIfExists('voluntarios');
    }
};