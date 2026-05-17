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
            $table->boolean('tiene_celular')->default(false)->after('celular');
            $table->boolean('sabe_usar_whatsapp')->default(false)->after('tiene_celular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adulto_mayor', function (Blueprint $table) {
            $table->dropColumn(['tiene_celular', 'sabe_usar_whatsapp']);
        });
    }
};
