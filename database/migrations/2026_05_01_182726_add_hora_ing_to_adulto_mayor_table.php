<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('adulto_mayor', 'hora_ing')) {
            Schema::table('adulto_mayor', function (Blueprint $table) {
                $table->time('hora_ing')->nullable()->after('fecha_ing');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('adulto_mayor', 'hora_ing')) {
            Schema::table('adulto_mayor', function (Blueprint $table) {
                $table->dropColumn('hora_ing');
            });
        }
    }
};
