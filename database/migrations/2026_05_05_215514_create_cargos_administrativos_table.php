<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cargos_administrativos', function (Blueprint $table) {
            $table->increments('cod_cargo_admin');
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });

        // También agregamos la columna a personal_admin para vincularlos
        Schema::table('personal_admin', function (Blueprint $table) {
            $table->unsignedInteger('cod_cargo_admin')->nullable()->after('cod_usu');
            $table->foreign('cod_cargo_admin')
                ->references('cod_cargo_admin')
                ->on('cargos_administrativos')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('personal_admin', function (Blueprint $table) {
            $table->dropForeign(['cod_cargo_admin']);
            $table->dropColumn('cod_cargo_admin');
        });
        Schema::dropIfExists('cargos_administrativos');
    }
};
