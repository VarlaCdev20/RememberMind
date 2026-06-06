<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            if (! Schema::hasColumn('adulto_mayor', 'cod_habitacion')) {
                $table->unsignedBigInteger('cod_habitacion')->nullable()->after('procedencia_ingreso');
                $table->foreign('cod_habitacion')
                    ->references('cod_habitacion')
                    ->on('habitaciones')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }

            if (! Schema::hasColumn('adulto_mayor', 'cod_cama')) {
                $table->unsignedBigInteger('cod_cama')->nullable()->after('cod_habitacion');
                $table->foreign('cod_cama')
                    ->references('cod_cama')
                    ->on('camas')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            if (Schema::hasColumn('adulto_mayor', 'cod_cama')) {
                $table->dropForeign(['cod_cama']);
                $table->dropColumn('cod_cama');
            }
            if (Schema::hasColumn('adulto_mayor', 'cod_habitacion')) {
                $table->dropForeign(['cod_habitacion']);
                $table->dropColumn('cod_habitacion');
            }
        });
    }
};
