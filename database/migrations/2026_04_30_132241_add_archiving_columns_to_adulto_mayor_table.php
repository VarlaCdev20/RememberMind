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
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->timestamp('archivado_en')->nullable();
            $table->text('motivo_archivado')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->dropColumn(['archivado_en', 'motivo_archivado']);
        });
    }
};
