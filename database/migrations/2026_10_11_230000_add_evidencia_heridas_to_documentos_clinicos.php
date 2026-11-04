<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_clinicos', function (Blueprint $table): void {
            $table->string('cod_herida', 20)->nullable()->index();
            $table->string('cod_curacion', 20)->nullable()->index();
            $table->foreign('cod_herida')->references('cod_herida')->on('heridas')->restrictOnDelete();
            $table->foreign('cod_curacion')->references('cod_curacion')->on('curaciones_herida')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos_clinicos', function (Blueprint $table): void {
            $table->dropForeign(['cod_herida']);
            $table->dropForeign(['cod_curacion']);
            $table->dropIndex(['cod_herida']);
            $table->dropIndex(['cod_curacion']);
            $table->dropColumn(['cod_herida', 'cod_curacion']);
        });
    }
};
