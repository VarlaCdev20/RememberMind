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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'calle')) {
                $table->string('calle', 150)->nullable()->after('ciudad');
            }
            if (!Schema::hasColumn('users', 'nro_domicilio')) {
                $table->string('nro_domicilio', 20)->nullable()->after('calle');
            }
            if (!Schema::hasColumn('users', 'ap_paterno_emergencia')) {
                $table->string('ap_paterno_emergencia', 100)->nullable()->after('parentesco_emergencia');
            }
            if (!Schema::hasColumn('users', 'ap_materno_emergencia')) {
                $table->string('ap_materno_emergencia', 100)->nullable()->after('ap_paterno_emergencia');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'calle',
                'nro_domicilio',
                'ap_paterno_emergencia',
                'ap_materno_emergencia'
            ]);
        });
    }
};
