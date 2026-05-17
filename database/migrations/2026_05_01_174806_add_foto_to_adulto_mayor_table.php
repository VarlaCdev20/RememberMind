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
        if (!Schema::hasColumn('adulto_mayor', 'foto')) {
            Schema::table('adulto_mayor', function (Blueprint $table) {
                $table->string('foto', 2048)->nullable()->after('cod_est_adul');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('adulto_mayor', 'foto')) {
            Schema::table('adulto_mayor', function (Blueprint $table) {
                $table->dropColumn('foto');
            });
        }
    }
};
