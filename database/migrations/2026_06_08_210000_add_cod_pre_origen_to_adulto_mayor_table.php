<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            if (! Schema::hasColumn('adulto_mayor', 'cod_pre_origen')) {
                $table->string('cod_pre_origen', 20)->nullable()->after('procedencia_ingreso');
            }
        });

        Schema::table('adulto_mayor', function (Blueprint $table) {
            if (Schema::hasColumn('adulto_mayor', 'cod_pre_origen')) {
                $table->foreign('cod_pre_origen')
                    ->references('cod_pre')
                    ->on('preadmisiones')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            if (Schema::hasColumn('adulto_mayor', 'cod_pre_origen')) {
                $table->dropForeign(['cod_pre_origen']);
                $table->dropColumn('cod_pre_origen');
            }
        });
    }
};
