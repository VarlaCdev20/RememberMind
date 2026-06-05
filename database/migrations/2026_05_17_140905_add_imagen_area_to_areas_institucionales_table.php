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
        if (
            Schema::hasTable('areas_institucionales') &&
            !Schema::hasColumn('areas_institucionales', 'imagen_area')
        ) {
            Schema::table('areas_institucionales', function (Blueprint $table) {
                $table->string('imagen_area')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('areas_institucionales') &&
            Schema::hasColumn('areas_institucionales', 'imagen_area')
        ) {
            Schema::table('areas_institucionales', function (Blueprint $table) {
                $table->dropColumn('imagen_area');
            });
        }
    }
};
